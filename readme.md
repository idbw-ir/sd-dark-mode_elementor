
# 🌙 دارک مود المنتور پیشرفته (SD Dark Mode)

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.1-blue.svg)](https://wordpress.org/plugins/sd-dark-mode/)
[![License](https://img.shields.io/badge/license-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-purple.svg)](https://php.net/)
[![WordPress Version](https://img.shields.io/badge/wordpress-%3E%3D%205.8-blue.svg)](https://wordpress.org/)

افزونه **دارک مود المنتور پیشرفته** یک راه‌حل کامل، سبک و حرفه‌ای برای اضافه کردن حالت شب (Dark Mode) به وب‌سایت‌های وردپرسی، به ویژه سایت‌های ساخته شده با المنتور است. این افزونه به شما اجازه می‌دهد تا هم رنگ‌های سراسری سایت و هم رنگ‌ها و تصاویر هر ویجت المنتور را به صورت جداگانه برای حالت تاریک و روشن تنظیم کنید.

---

## ✨ ویژگی‌ها

- 🎨 **کنترل سراسری رنگ‌ها:** تغییر رنگ‌های Global Colors المنتور و متغیرهای CSS قالب برای حالت دارک مود
- 🧩 **کنترل ویجتی در المنتور:** اضافه شدن بخش "🌙 دارک مود" به تنظیمات هر ویجت المنتور
- 🖼️ **جایگزینی هوشمند تصاویر:** امکان آپلود تصویر جداگانه برای حالت دارک مود
- ⚙️ **تشخیص خودکار:** تشخیص تنظیمات دارک مود سیستم عامل کاربر (prefers-color-scheme)
- ⏰ **زمان‌بندی هوشمند:** فعال‌سازی بر اساس ساعت روز و شب
- 🍪 **ذخیره انتخاب کاربر:** ذخیره انتخاب کاربر در کوکی برای بازدیدهای بعدی
- 🚀 **بهینه‌سازی شده:** تولید CSS استاتیک و بدون تاثیر بر سرعت سایت
- 🛠️ **API جاوااسکریپت:** توابع `sdTheme.toggle()` و `sdTheme.set()` برای توسعه‌دهندگان
- 🌐 **پشتیبانی از RTL:** کاملاً سازگار با سایت‌های فارسی و راست‌چین

---

##  نصب

### روش اول: از طریق مخزن وردپرس (توصیه شده)

1. به پیشخوان وردپرس بروید
2. از منوی **افزونه‌ها > افزودن**، عبارت "SD Dark Mode" را جستجو کنید
3. روی **نصب اکنون** و سپس **فعال‌سازی** کلیک کنید
4. به منوی **تنظیمات > 🌙 دارک مود** بروید

### روش دوم: نصب دستی

1. فایل ZIP افزونه را از [صفحه releases](https://github.com/hosseinam/sd-dark-mode/releases) دانلود کنید
2. در پیشخوان وردپرس به **افزونه‌ها > افزودن > بارگذاری افزونه** بروید
3. فایل ZIP را انتخاب و روی **نصب اکنون** کلیک کنید
4. افزونه را فعال کنید

---

## 🚀 نحوه استفاده

### 1. تنظیمات سراسری

پس از فعال‌سازی افزونه، به مسیر **تنظیمات > 🌙 دارک مود** بروید. در این صفحه می‌توانید:

- **حالت پیش‌فرض** را انتخاب کنید (سیستم، زمان‌بندی، همیشه روشن، همیشه تاریک)
- **رنگ‌های سراسری المنتور** را برای حالت دارک مود تنظیم کنید
- **متغیرهای CSS قالب** خود را تعریف کنید
- **متغیرهای دلخواه** اضافه کنید

### 2. تنظیمات ویجت‌ها در المنتور

1. صفحه مورد نظر را با المنتور ویرایش کنید
2. روی هر ویجت کلیک کنید
3. در پایین پنل تنظیمات، بخش جدید **"🌙 دارک مود"** را مشاهده خواهید کرد
4. رنگ‌ها یا تصاویر مخصوص حالت دارک مود را تنظیم کنید

### 3. اضافه کردن دکمه تغییر تم

برای اضافه کردن دکمه تغییر تم در هدر سایت، از ویجت HTML المنتور استفاده کنید:

```html
<button onclick="sdTheme.toggle()" class="theme-toggle">
    🌙 تغییر تم
</button>
```

یا با استفاده از JavaScript:

```javascript
// تغییر به حالت تاریک
sdTheme.set('dark');

// تغییر به حالت روشن
sdTheme.set('light');

// بازگشت به حالت خودکار
sdTheme.set('auto');

// دریافت حالت فعلی
console.log(sdTheme.get());

// دریافت انتخاب کاربر
console.log(sdTheme.choice());

// تغییر بین دو حالت
sdTheme.toggle();
```

### 4. استفاده در قالب (Theme)

برای نمایش تصاویر با نسخه دارک مود در قالب خود، از تابع کمکی `sd_dark_img()` استفاده کنید:

```php
<?php
// نمایش تصویر با نسخه دارک مود
echo sd_dark_img(
    get_template_directory_uri() . '/images/logo-light.png',
    get_template_directory_uri() . '/images/logo-dark.png',
    ['class' => 'site-logo', 'alt' => 'لوگو سایت']
);
?>
```

---

## 🛠️ راهنمای توسعه‌دهندگان

### هوک‌های فیلتر (Filters)

```php
// تغییر متغیرهای پیش‌فرض قالب
add_filter('sd_dark_theme_vars', function($vars) {
    $vars['--custom-variable'] = 'متغیر سفارشی';
    return $vars;
});

// تغییر مقادیر پیش‌فرض تنظیمات
add_filter('sd_dark_defaults', function($defaults) {
    $defaults['cookie_days'] = 30;
    return $defaults;
});
```

### رویدادهای جاوااسکریپت

```javascript
// گوش دادن به تغییر تم
document.addEventListener('sdthemechange', function(event) {
    console.log('تم تغییر کرد به:', event.detail.theme);
    console.log('انتخاب کاربر:', event.detail.choice);
});
```

### کلاس‌های CSS

افزونه به صورت خودکار کلاس `dark` را به تگ `<body>` اضافه می‌کند. همچنین تگ `<html>` دارای ویژگی `data-sd-theme` خواهد بود:

```css
/* استایل‌های سفارشی برای حالت دارک مود */
body.dark .my-custom-element {
    background-color: #1a1a1a;
    color: #ffffff;
}

/* یا با استفاده از data attribute */
html[data-sd-theme="dark"] .my-custom-element {
    background-color: #1a1a1a;
}
```

---

## 📁 ساختار پوشه‌ها

```text
sd-dark-mode/
├── sd-dark-mode.php          # فایل اصلی افزونه
├── readme.txt                # فایل راهنمای مخزن وردپرس
├── readme.md                 # این فایل (راهنمای گیت‌هاب)
├── includes/
│   ├── class-plugin.php      # کلاس اصلی و مدیریت هوک‌ها
│   ├── class-settings.php    # مدیریت تنظیمات و صفحه ادمین
│   ├── class-frontend.php    # مدیریت خروجی فرانت‌اند
│   └── class-elementor.php   # یکپارچگی با المنتور
└── assets/
    ├── css/                  # فایل‌های CSS افزونه
    ── js/                   # فایل‌های JavaScript افزونه
```

---

## 🤝 مشارکت

مشارکت‌های شما با کمال میل پذیرفته می‌شود! برای مشارکت:

1. از مخزن Fork بگیرید
2. یک شاخه (Branch) جدید ایجاد کنید (`git checkout -b feature/AmazingFeature`)
3. تغییرات خود را Commit کنید (`git commit -m 'Add some AmazingFeature'`)
4. به شاخه اصلی Push کنید (`git push origin feature/AmazingFeature`)
5. یک Pull Request باز کنید

لطفاً قبل از ارسال Pull Request، مطمئن شوید که کد شما با استانداردهای کدنویسی وردپرس سازگار است.

---

## 🐛 گزارش باگ و درخواست ویژگی

اگر باگ یا مشکل پیدا کردید، یا ویژگی جدیدی در ذهن دارید، لطفاً در بخش [Issues](https://github.com/hosseinam/sd-dark-mode/issues) گزارش دهید.

---

## 📝 تغییرات (Changelog)

### نسخه 1.0.1 (28 اکتبر 2026)
- ✅ انتشار اولیه افزونه
- ✅ پنل تنظیمات سراسری برای متغیرهای CSS و رنگ‌های المنتور
- ✅ کنترل‌های دارک مود برای ویجت‌های المنتور (رنگ و تصویر)
- ✅ پشتیبانی از تشخیص خودکار تم سیستم عامل
- ✅ زمان‌بندی شب/روز
- ✅ بهینه‌سازی عملکرد و تولید CSS استاتیک
- ✅ API جاوااسکریپت برای توسعه‌دهندگان

---

## 📄 لایسنس

این افزونه تحت لایسنس [GPLv2 یا بالاتر](https://www.gnu.org/licenses/gpl-2.0.html) منتشر شده است.

---

## 👨‍💻 نویسنده

**Hossein Azimy Birjandy**

- 🌐 وب‌سایت: [hosseinam.ir](https://hosseinam.ir)

---

<p align="center">
    ساخته شده با ❤️ توسط <a href="https://hosseinam.ir">Hossein Azimy Birjandy</a>
</p>
```