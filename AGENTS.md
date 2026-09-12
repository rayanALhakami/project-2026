# فكرة المشروع 
تطبيق لتتبع المصاريف مدعوم بالذكاء الاصطناعي

# المتطلبات وترتيبها  MoSCoW style


# ملخص المتطلبات

| القرار | البند |
|--------|--------|
| تتبع المصاريف الشخصية (Personal Budgeting) | الهدف |
| متصفح (Web app) | المنصة |
| Email + Password | توثيق الدخول |
| يدوي فقط | إدخال المصاريف |
| نعم (مصروفات + دخل) | تتبع الإيرادات |
| عملة واحدة - SAR (ريال سعودي) | العملة |
| العربية فقط | اللغة |
| سحابي (Cloud Sync) | تخزين البيانات |
| بدون | المرفقات |
| فئات فقط (Categories only) | التصنيف |
| بدون | مشاركة المصروفات |
| بدون | أهداف ادخارية |
| بدون | الميزانية (Budgeting) |
| رسوم بيانية متقدمة (Pie charts, Bar charts, Trends) | التقارير |
| مساعد دردشة وكيل (Agentic AI Chat) | AI |
| في شهر واحد MVP | المدة |

---

# متطلبات MVP (Must Have)

1. **تسجيل الدخول** - Email + Password
2. **إدخال المصاريف والدخل** - يدوي مع فئات
3. **تصنيف تلقائي بالذكاء الاصطناعي** - عند إدخال المصروف
4. **وكيل ذكاء اصطناعي محادثي** - يسأل ويجاوب عن الفلوس، يضيف مصاريف، ينصح، يكتشف الحالات الشاذة
5. **تصورات بيانية** - رسوم بيانية تفاعلية للمصروفات والدخل
6. **Cloud Sync** - حفظ البيانات في السحابة

---

# MoSCoW Prioritization

## Must Have (MVP)
- Authentication (Email + Password)
- Manual expense/income entry with categories
- AI chat assistant (Q&A, add transactions, anomaly detection, actionable insights)
- Rich visualizations (pie charts, bar charts, trends)
- Cloud data sync
- Arabic UI

## Should Have
- AI auto-categorization on entry
- Spending trends and insights
- Data export (CSV/PDF)

## Could Have
- Bank import / Open Banking (STC Pay)
- Multi-currency support
- Receipt OCR scanning
- English language support
- Expense splitting

## Won't Have (v1)
- Attachments / receipt images
- Tags (beyond categories)
- Savings goals
- Budgeting/envelope system
- Social/multi-user features

---

# AI Agent Capabilities (MVP)

1. **Financial Q&A** - "كم صرفت على الأكل الشهر اللي فات؟"
2. **Add transactions via chat** - "أضف 45 ريال مشتريات بقالة"
3. **Budgeting advice** - "صرفك على المطاعم زائد هذا الشهر"
4. **Anomaly detection** - "هذه المصروف 500 ريال غير معتاد"
5. **Trigger actions** - "صنف لي هذا المصروف" أو "أظهر تقرير"

# اختيار التقنيات

- Laravel
- Inertia (Laravel svelte template)
- TailwindCSS


