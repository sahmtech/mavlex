# Waiter App — Tables, Orders & Realtime

Documentation for the Flutter waiter app.  
Auth, business, branch (location), and floor scoping are enforced on every endpoint.

---

## 1. Base URL & authentication

Use the **same Passport token** already used by the Connector module. Do **not** create a new login.

| Item | Value |
|---|---|
| Header | `Authorization: Bearer {access_token}` |
| Header | `Accept: application/json` |
| Header | `Content-Type: application/json` |
| Auth | Laravel Passport (`auth:api`) |

`business_id` is taken from the token user. The client must **not** send `business_id`.

### Two equivalent prefixes

Same controller, same responses:

```
{APP_URL}/api/...
{APP_URL}/connector/api/...
```

Examples:

```
GET  /api/get-tables?establishment_id=1
GET  /connector/api/get-tables?establishment_id=1
```

Use **one** prefix consistently. Prefer `/api/...` if the app already talks to `/api`.

### Do not use the old CRUD table API for the waiter floor

| Endpoint | Purpose |
|---|---|
| `GET /connector/api/table` | Legacy Connector CRUD list (no occupancy, no waiter, no order) |
| `GET /api/get-tables` | **Waiter floor** — use this |

---

## 2. Scope: business, branch, floor

| Field in API | Meaning in POS |
|---|---|
| *(from token)* | `business_id` |
| `establishment_id` **or** `location_id` | Branch / business location |
| `floor_id` | Floor |

Rules:

- If `establishment_id` is sent, only tables of that branch are returned.
- If `floor_id` is sent, only tables of that floor.
- If `establishment_id` / `floor_id` is sent on `show` / `change-status` / `new-order` and the table does **not** belong to them → error `table_not_in_scope`.
- Users without “all locations” only see permitted branches.

Always send `establishment_id` from the waiter’s selected branch.

---

## 3. Table status

| `status` | `status_label` | Meaning |
|---:|---|---|
| `0` | `available` | Free, no open order |
| `1` | `reserved` | Reserved for a guest |
| `2` | `notAvailable` | Occupied (open order) |

Operational rules:

1. Occupied (`2`) is set **automatically** when `POST /new-order` succeeds. `assigned_waiter_id` = the token user.
2. Sending `new-order` on an already occupied table **updates the same invoice** (replaces lines). It does **not** create a second open order.
3. Manual `change-status` is **blocked** while an open order exists.
4. Table returns to `0` and waiter is cleared only when the order is **served** or **cancelled**.
5. Status `1` **requires** guest name (`reserved_guest_name` or `guest_name`). Phone is optional.

---

## 4. Endpoints

### 4.1 List tables

```
GET /api/get-tables
```

Query:

| Param | Required | Notes |
|---|---|---|
| `establishment_id` | recommended | Branch id (`location_id` alias works) |
| `floor_id` | no | Filter by floor |
| `waiter_id` | no | Only tables assigned to this waiter |

**200**

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "business_id": 1,
      "name": "T-04",
      "description": null,
      "establishment_id": 1,
      "location_id": 1,
      "floor_id": 2,
      "floor_name": "الأرضي",
      "capacity": 4,
      "seats": 4,
      "status": 2,
      "status_label": "notAvailable",
      "assigned_waiter_id": 9,
      "assigned_waiter": {
        "id": 9,
        "name": "Ali Waiter"
      },
      "reservation": null,
      "active_order": {
        "id": 881,
        "invoice_no": "00088",
        "source": "local",
        "is_internal_table_order": true,
        "custom_field_1": "table_order",
        "is_suspend": 1,
        "is_kitchen_order": 1,
        "status": "final",
        "payment_status": "due",
        "res_order_status": "received",
        "res_table_id": 12,
        "res_waiter_id": 9,
        "waiter_name": "Ali Waiter",
        "notes": "بدون بصل",
        "final_total": 45.5,
        "items_count": 2,
        "items": [
          {
            "id": 1001,
            "product_id": 30,
            "variation_id": 55,
            "name": "شاي",
            "quantity": 2,
            "unit_price_inc_tax": 5,
            "line_total": 10,
            "sell_line_note": null
          }
        ],
        "created_at": "2026-09-10 14:20:00"
      }
    }
  ]
}
```

When `status = 1`:

```json
"reservation": {
  "guest_name": "أحمد",
  "guest_phone": "05xxxxxxxx",
  "note": "الساعة 8"
}
```

When free: `reservation` and `active_order` are `null`.

---

### 4.2 Table details

```
GET /api/tables/{id}?establishment_id=1&floor_id=2
```

Same table object as list, with **full order lines**.  
Also may include `active_reservation` (calendar booking if one is in progress).

---

### 4.3 Change status

```
POST /api/change-status/{id}
```

**Available / occupied (no open order)**

```json
{
  "status": 0,
  "establishment_id": 1,
  "floor_id": 2
}
```

`status` can also be sent as `table_status`.

**Reserve (`1`) — guest name is required**

```json
{
  "status": 1,
  "reserved_guest_name": "أحمد",
  "reserved_guest_phone": "05xxxxxxxx",
  "reserved_note": "الساعة 8",
  "establishment_id": 1,
  "floor_id": 2
}
```

Aliases for name: `guest_name`, `customer_name`  
Aliases for phone: `guest_phone`, `phone`, `mobile`

**200**

```json
{
  "success": true,
  "data": { }
}
```

`data` is the full table object (same shape as list item).

**Errors (HTTP 400)**

```json
{
  "error": {
    "message": "أدخل اسم الشخص الذي سيحجز الطاولة"
  }
}
```

Typical messages:

- open order exists → cannot change status manually  
- reserved without name → guest required  
- wrong branch/floor → table not in scope  

---

### 4.4 New order (open or update)

```
POST /api/new-order
```

Creates a **suspended POS sale** (`source = local`, kitchen order) on the table, status → `2`.  
If the table already has an open order, **lines are replaced** (`created: false`).

```json
{
  "table_id": 12,
  "establishment_id": 1,
  "floor_id": 2,
  "notes": "بدون بصل",
  "staff_note": null,
  "items": [
    {
      "variation_id": 55,
      "quantity": 2,
      "unit_price": 5,
      "unit_price_inc_tax": 5,
      "sell_line_note": null,
      "modifiers": [
        {
          "variation_id": 90,
          "quantity": 1,
          "unit_price": 2
        }
      ]
    }
  ]
}
```

Aliases:

| Field | Also accepted |
|---|---|
| `table_id` | `res_table_id`, `table` |
| `items` | `products` |
| `notes` | `sale_note`, `additional_notes` |
| `establishment_id` | `location_id` |
| item `quantity` | `qty` |
| item `variation_id` | `id` |

Prices are optional; if omitted, POS sell price of the variation is used.

**200**

```json
{
  "success": true,
  "created": true,
  "data": { },
  "table": { }
}
```

- `data` = order object  
- `table` = updated table (`status: 2`)  
- `created: false` = existing order was updated  

Products/variations: use existing Connector APIs  
`GET /connector/api/product` and `GET /connector/api/variation`.

---

### 4.5 Update order / mark served

```
POST /api/update-orders/{id}
```

`{id}` = **order id** (transaction) **or** table id (fallback).

**Update items** (same body as `new-order`):

```json
{
  "table_id": 12,
  "establishment_id": 1,
  "items": [ { "variation_id": 55, "quantity": 1 } ]
}
```

**Mark served** (releases the table to `0`):

```json
{
  "status": "served"
}
```

Also accepted: `"status": "completed"` or `"res_order_status": "served"`.

---

### 4.6 Cancel order

```
POST /api/cancel-order
```

```json
{
  "order_id": 881
}
```

or

```json
{
  "table_id": 12
}
```

Unpaid table orders are deleted like a parked POS bill.  
Table → `0`, waiter cleared.

**200**

```json
{ "success": true }
```

---

## 5. Cashier / POS distinction

Waiter orders are real sales invoices, marked as **internal table orders**:

| DB / API field | Value |
|---|---|
| `source` | `local` |
| `custom_field_1` | `table_order` |
| `is_suspend` | `1` until cashier collects |
| `is_kitchen_order` | `1` |
| `is_internal_table_order` | `true` |

Do not treat them as a separate order engine.

---

## 6. Realtime (sockets)

Every waiter or dashboard action broadcasts:

| Event name | When |
|---|---|
| `table:updated` | Status, waiter, reservation, or occupancy changed |
| `order:created` | First `new-order` on a free table |
| `order:updated` | Lines changed, served, or cancelled |

### 6.1 Channel

Public Laravel channel (Echo / Pusher):

```
table-orders.{business_id}
```

Example: business `1` → `table-orders.1`

`business_id` comes from `GET /connector/api/user/loggedin` (or the login user object).

### 6.2 Event payloads

**`table:updated`**

```json
{
  "table_id": 12,
  "status": 2,
  "order_id": 881,
  "updated_by": 9,
  "location_id": 1,
  "floor_id": 2
}
```

Fields vary; always present: `table_id`.  
On release after serve/cancel: `status: 0`.

**`order:created` / `order:updated`**

```json
{
  "table_id": 12,
  "order_id": 881,
  "status": 2
}
```

Served:

```json
{
  "order_id": 881,
  "table_id": 12,
  "res_order_status": "served"
}
```

Cancelled:

```json
{
  "order_id": 881,
  "table_id": 12,
  "cancelled": true
}
```

**App behaviour:** on any of these events, refresh `GET /get-tables` (or patch the matching table). Do not wait for a pull-to-refresh.

---

## 7. Socket setup for Flutter

The backend supports **two** transports. Use the one the server enables.

### Option A — Pusher / Laravel Echo (recommended if POS notifications already use Pusher)

Server `.env`:

```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
PUSHER_SCHEME=https
PUSHER_PORT=443
```

Flutter (`pusher_channels_flutter`):

```dart
await pusher.init(
  apiKey: pusherKey,
  cluster: pusherCluster,
);

await pusher.subscribe(channelName: 'table-orders.$businessId');

pusher.onEvent = (event) {
  // event.eventName is: table:updated | order:created | order:updated
  if (event.eventName == 'table:updated' ||
      event.eventName == 'order:created' ||
      event.eventName == 'order:updated') {
    refreshTables();
  }
};

await pusher.connect();
```

Laravel Echo names the event **without** an extra namespace. If you use `laravel_echo` for Flutter, listen as:

```
.table:updated
.order:created
.order:updated
```

(leading dot = custom `broadcastAs` name).

### Option B — Socket.IO (if the project has a Node Socket.IO bridge)

Server `.env`:

```
SOCKET_IO_URL=https://sockets.your-domain.com
```

On each action Laravel POSTs:

```
POST {SOCKET_IO_URL}/emit
Content-Type: application/json

{
  "event": "table:updated",
  "room": "table-orders.1",
  "payload": { "table_id": 12, "status": 2, "order_id": 881 }
}
```

Flutter (`socket_io_client`):

```dart
final socket = io(
  socketBaseUrl,
  OptionBuilder().setTransports(['websocket']).enableAutoConnect().build(),
);

socket.onConnect((_) {
  socket.emit('join', 'table-orders.$businessId');
});

socket.on('table:updated', (_) => refreshTables());
socket.on('order:created', (_) => refreshTables());
socket.on('order:updated', (_) => refreshTables());
```

The Node server must:

1. Accept `POST /emit` with `{ event, room, payload }`
2. `io.to(room).emit(event, payload)`
3. On client `join`, `socket.join(room)`

If `SOCKET_IO_URL` is empty, this path is skipped. Pusher still works if `BROADCAST_DRIVER=pusher`.

### Option C — Fallback polling

If neither Pusher nor Socket.IO is configured (`BROADCAST_DRIVER=log` or `null`):

- Poll `GET /get-tables` every **5–8 seconds** while the floor screen is open.
- The web dashboard does the same.

---

## 8. Suggested Flutter flow

1. Login (existing Passport).
2. Read `business_id` + selected `establishment_id` (branch).
3. Subscribe to `table-orders.{business_id}`.
4. `GET /get-tables?establishment_id={branch}&floor_id={optional}`.
5. Tap free table → cart → `POST /new-order`.
6. Tap occupied table → edit lines → `POST /new-order` again (same table) **or** `POST /update-orders/{orderId}`.
7. Reserve → modal (name required, phone optional) → `POST /change-status/{id}` with `status: 1`.
8. Serve → `POST /update-orders/{orderId}` with `status: "served"`.
9. Cancel → `POST /cancel-order` with `order_id` or `table_id`.
10. On socket event → refresh list.

---

## 9. Error contract

Failures from waiter APIs:

```
HTTP 400
{
  "error": {
    "message": "human readable text"
  }
}
```

Unauthorized token: standard Passport `401`.

---

## 10. Quick checklist for the Flutter developer

- [ ] Bearer token = existing Connector token  
- [ ] Always pass `establishment_id`  
- [ ] Map UI: 0 available / 1 reserved / 2 occupied (`notAvailable`)  
- [ ] Reserve requires `reserved_guest_name`  
- [ ] `new-order` on busy table updates, does not duplicate  
- [ ] Listen `table-orders.{business_id}` for `table:updated`, `order:created`, `order:updated`  
- [ ] Do not call legacy `GET /connector/api/table` for the waiter floor  
