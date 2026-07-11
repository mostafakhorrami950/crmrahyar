# معماری پلتفرم رزرو هتل - نسخه ۱۰

## ساختار کلی
- Domain-Driven + Service-Oriented + API-First
- وبسایت روی ریشه `/`، CRM در `/crm` (بدون تغییر)
- دیتابیس مشترک، بدون داده تکراری
- CRM = Single Source of Truth

## اصول کلیدی
1. هیچ Business Logic در Controller یا View
2. تمام وابستگی‌ها از طریق DI + Interface
3. Hard Code ممنوع - تنظیمات از DB
4. API-Versioned: /api/v1/
5. Plugin-Friendly architecture
6. Migration-only database changes
7. Soft Delete + Audit Trail
8. Concurrency Control + Idempotency
9. Queue برای پردازش‌های زمان‌بر
10. Feature Flags
11. Smart Cache با Tags
12. Entity SEO + AI Search Optimization

## واحد پول
- ذخیره‌سازی: IRR (ریال) - استاندارد ISO 4217
- نمایش: تومان (÷۱۰)
- API: { amount, currency, display_amount, display_currency }

## Reservation Hold
- Hold با reservation_token و expires_at
- Booking از ابتدای Hold با وضعیت draft
- Auto-release توسط Queue بعد از انقضا
- Pre-payment validation: بررسی مجدد availability + pricing + campaign
- Heartbeat: GET هر ۱۵-۲۰ ثانیه (~200 bytes)
- Refresh: POST فقط هنگام تغییر یا کلیک پرداخت

## Workflow (11 وضعیت)
draft → reserved(Hold) → waiting_payment → payment_processing → paid → confirmed
                                                                 → checked_in → checked_out → completed
reserved → expired (Queue)
waiting_payment → cancelled
payment_processing → payment_failed → waiting_payment
any → cancelled → refunded