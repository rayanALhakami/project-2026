---
paths:
    - 'resources/**'
---

# Resources

## Arabic/Latin typography: IBM Plex Sans Arabic + Inter

Arabic UI uses IBM Plex Sans Arabic (closest open equivalent to SF Arabic); Latin uses Inter. Keep BlinkMacSystemFont out of the font stack (it hijacks Arabic with Segoe UI on Windows Chrome). Never apply negative letter-spacing in RTL — Arabic joins break; body line-height for Arabic is 1.6. Weights are loaded from Google Fonts in resources/views/app.blade.php.
