# تعديلات الكاشير: زيادة المخزون عند المردود وتخزين الضرائب

هذا الملف يشرح التعديلات المطلوبة في تطبيق الكاشير حتى يطابق السيرفر.
السيرفر نفسه أصبح يطبّق نفس القواعد على واجهة الـ API.

المصادقة كما هي: `Authorization: Bearer {token}`  
الأساس: `/connector/api`

---

## 1. أعمدة جدول `transactions`

أضيفت هذه الأعمدة، ويجب تعبئتها عند حفظ فاتورة البيع وعند حفظ المردود:

| العمود | المعنى |
| --- | --- |
| `tax_id` | رقم ضريبة النسبة العادية (مثل ضريبة القيمة المضافة). |
| `tax_amount` | مبلغ الضريبة العادية لكامل الفاتورة أو المردود. |
| `tax2_id` | رقم الضريبة المركبة التي لها حد أدنى (مثل ضريبة التبغ). |
| `tax2_amount` | مبلغ الضريبة المركبة لكامل الفاتورة أو المردود. |
| `discount_includes_tax` | `1` الخصم يُخصم من الإجمالي شامل الضريبة. `0` الخصم يُخصم من السعر قبل الضريبة. |

سطر البيع يبقى كما هو: `transaction_sell_lines.tax_id` هو رقم الضريبة أو مجموعة الضرائب الخاصة بالصنف، و`item_tax` هو مبلغ الضريبة للوحدة الواحدة (العادية + المركبة).

لا تُجمع النسبتان في حقل واحد. كل ضريبة تُخزَّن في عمودها.

---

## 2. من أين تأتي بيانات الضريبة

`GET /connector/api/tax` و`GET /connector/api/tax/{id}` يرجعان الآن `sub_taxes` مع `min_amount`.

- إذا كانت الضريبة مجموعة (`is_tax_group = 1`) وفيها ضرائب فرعية:
  - الضريبة التي `min_amount` فيها رقم هي الضريبة المركبة. تُخزَّن في `tax2_id` و`tax2_amount`.
  - الضريبة التي `min_amount` فيها `null` هي الضريبة العادية. تُخزَّن في `tax_id` و`tax_amount`.
- إذا لم تكن مجموعة: تُحسب كنسبة من سعر الوحدة بعد خصم السطر، وتُخزَّن في `tax_id` و`tax_amount`. يبقى `tax2_id = null` و`tax2_amount = 0`.

أرسل `products.*.tax_rate_id` على كل صنف. هذا هو مصدر التخزين الجديد.
ضريبة رأس الفاتورة `tax_rate_id` تُستخدم فقط إذا لم يحمل أي صنف ضريبة، وتُحفظ عندها في `tax_id` و`tax_amount`.

---

## 3. حساب ضريبة السطر

احسبها على سعر الوحدة بعد خصم السطر، وليس قبل الخصم.

```
net = unit_price بعد خصم السطر
إذا كان الخصم fixed: net = unit_price - line_discount_amount
إذا كان percentage: net = unit_price * (100 - line_discount_amount) / 100
إذا نزل net تحت الصفر فاجعله 0
```

### مجموعة ضرائب

```
compound = الضريبة الفرعية التي لها min_amount
plain    = الضريبة الفرعية التي min_amount فيها فارغ

إذا كان net < compound.min_amount وكان الحد أكبر من صفر:
    compound_portion = compound.min_amount
وإلا:
    compound_portion = net * (compound.amount / 100)

plain_portion = (net + compound_portion) * (plain.amount / 100)

item_tax = compound_portion + plain_portion
unit_price_inc_tax = net + item_tax
```

على رأس الفاتورة:

```
tax2_id     = compound.id
tax2_amount = مجموع (compound_portion * quantity) لكل الأسطر
tax_id      = plain.id
tax_amount  = مجموع (plain_portion * quantity) لكل الأسطر
```

### مثال

سعر الوحدة بعد خصم السطر = 20، والكمية = 2.  
الضريبة المركبة: 100% وبحد أدنى 25.  
ضريبة القيمة المضافة: 15% ومن دون حد.

السعر أقل من الحد، لذلك:

```
compound_portion = 25
plain_portion    = (20 + 25) * 0.15 = 6.75
item_tax         = 31.75
unit_price_inc_tax = 51.75

tax2_amount = 25 * 2 = 50
tax_amount  = 6.75 * 2 = 13.50
```

إذا كان السعر 40 (وصل إلى الحد):

```
compound_portion = 40 * 100% = 40
plain_portion    = (40 + 40) * 15% = 12
unit_price_inc_tax = 92
```

### ضريبة عادية واحدة

```
item_tax = net * (rate / 100)
unit_price_inc_tax = net + item_tax
tax_amount = مجموع (item_tax * quantity)
tax2_id = null
tax2_amount = 0
```

---

## 4. خصم الفاتورة

أرسل مع البيع ومع المردود:

```
discount_type: fixed أو percentage
discount_amount: قيمة الخصم أو نسبته
discount_includes_tax: 1 أو 0
```

إذا لم يُرسل الحقل في البيع، السيرفر يعتبره `1` حتى يبقى سلوك الـ API السابق (الخصم من الإجمالي شامل الضريبة).

```
inclusive = مجموع (unit_price_inc_tax * quantity)
exclusive = مجموع (net * quantity)

إذا discount_includes_tax = 1:
    أساس الخصم = inclusive
    final_total = inclusive - مبلغ الخصم

إذا discount_includes_tax = 0:
    أساس الخصم = exclusive
    after_exclusive = exclusive - مبلغ الخصم
    ratio = after_exclusive / exclusive
    final_total = after_exclusive + ((inclusive - exclusive) * ratio)
```

مبلغ الخصم إذا كان النوع `percentage` هو `الأساس * النسبة / 100`.  
لا تسمح للإجمالي أن ينزل تحت الصفر، ثم أضف الشحن ورسوم التغليف.

`tax_amount` و`tax2_amount` المخزَّنان هما مجموع ضرائب الأسطر قبل خصم رأس الفاتورة. علامة `discount_includes_tax` هي التي تقول كيف طُبّق الخصم على `final_total`.

---

## 5. بيع جديد أو تعديل بيع

`POST /connector/api/sell`  
`PUT /connector/api/sell/{id}`

الحقول الجديدة على رأس الفاتورة وفي كل صنف:

```json
{
  "sells": [
    {
      "location_id": 1,
      "contact_id": 1,
      "transaction_date": "2026-11-09 10:00:00",
      "status": "final",
      "discount_type": "fixed",
      "discount_amount": 0,
      "discount_includes_tax": 1,
      "products": [
        {
          "product_id": 17,
          "variation_id": 58,
          "quantity": 2,
          "unit_price": 20,
          "tax_rate_id": 4,
          "discount_amount": 0,
          "discount_type": "fixed"
        }
      ],
      "payments": [
        { "amount": 103.5, "method": "cash" }
      ]
    }
  ]
}
```

`tax_rate_id` هنا رقم مجموعة الضرائب المرتبطة بالصنف. السيرفر يقسمها ويحفظ:

- `transactions.tax_id` / `transactions.tax_amount`
- `transactions.tax2_id` / `transactions.tax2_amount`
- `transactions.discount_includes_tax`

عند قراءة الفاتورة لاحقًا ستظهر هذه الحقول داخل كائن الحركة.

---

## 6. المردود وزيادة المخزون

`POST /connector/api/sell-return`

```json
{
  "transaction_id": 157,
  "transaction_date": "2026-11-09 12:00:00",
  "discount_type": "fixed",
  "discount_amount": 0,
  "discount_includes_tax": 1,
  "products": [
    {
      "sell_line_id": 901,
      "quantity": 1,
      "unit_price": 20,
      "tax_rate_id": 4
    }
  ]
}
```

المطلوب فعليًا هو `transaction_id` و`products.*.sell_line_id` و`products.*.quantity`.
إذا لم يُرسل السعر أو الضريبة، يأخذ السيرفر `unit_price` و`unit_price_inc_tax` و`item_tax` و`tax_id` من سطر البيع الأصلي.
إذا غيّر الكاشير سعر المرتجع، أرسل `unit_price` و`item_tax` مع الكمية حتى لا تبقى ضريبة السطر القديم.

إذا لم يُرسل `discount_includes_tax`، تُنسخ القيمة من فاتورة البيع الأصلية.

المردود يُحفظ كحركة جديدة:

```
type = sell_return
status = final
return_parent_id = رقم فاتورة البيع
```

ويُخزَّن فيها نفس تقسيم الضرائب: `tax_id`، `tax_amount`، `tax2_id`، `tax2_amount`، `discount_includes_tax`.
المبالغ هنا عن الكمية المرتجعة فقط، وليست عن الفاتورة كاملة.

### ماذا يحدث للمخزون

لكل سطر مرتجع، إذا كان `enable_stock = 1`:

1. زيادة `quantity_returned` في سطر البيع الأصلي بمقدار الكمية المرتجعة.
2. زيادة `variation_location_details.qty_available` لنفس المنتج والتنويعة وفرع الفاتورة بمقدار الكمية المرتجعة.
3. تخفيض `quantity_sold` في سطر الشراء المرتبط، وزيادة `qty_returned` في جدول الربط `transaction_sell_lines_purchase_lines`.

لمنتج الكومبو: مكونات الكومبو ترجع للمخزون بهذه النسبة:

```
كمية المكون المرتجعة = (كمية المكون في سطر البيع / كمية سطر الأب) * كمية الأب المرتجعة
```

ثم تُزاد `qty_available` لكل مكون. منتج الكومبو نفسه يُزاد فقط إذا كان `enable_stock = 1`.

إذا كان تطبيق الكاشير يخصم المخزون محليًا عند البيع، فعند المردود يجب أن يعكس ذلك محليًا بنفس القواعد أعلاه، ثم يرسل الطلب للسيرفر. السيرفر سيزيد المخزون مرة أخرى في قاعدة البيانات، لذلك لا تُرسل حركة مخزون ثانية غير طلب المردود.

قائمة المردودات: `GET /connector/api/list-sell-return?sell_id={id}`  
وستظهر فيها حقول الضريبة الجديدة لأن الرد يعيد صف الحركة كما هو.

---

## 7. ما ينفَّذ في تطبيق الكاشير

1. عند بناء سطر البيع احسب `item_tax` و`unit_price_inc_tax` بالمعادلات في القسم 3، وأرسل `tax_rate_id` لكل صنف.
2. أرسل `discount_includes_tax` مع كل بيع وكل مردود، واحسب `final_total` حسب القسم 4.
3. بعد حفظ البيع اقرأ `tax_id` و`tax_amount` و`tax2_id` و`tax2_amount` من الرد واعرضهما في الفاتورة. لا تجمعهما في خانة ضريبة واحدة.
4. عند المردود أرسل `sell_line_id` والكمية، واترك السيرفر يزيد المخزون ويقسم ضريبة الكمية المرتجعة.
5. حدّث شاشة المخزون في الكاشير بعد نجاح المردود: أضف الكمية المرتجعة للصنف، أو أعد قراءة الرصيد من `GET /connector/api/product`.
6. اعتمد على `GET /connector/api/tax` لقراءة `min_amount` والضرائب الفرعية، ولا تثبّت النسب داخل التطبيق.
