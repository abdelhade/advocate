# Advocate UI & Design System Guidelines

Strict rules for all user interface components and pages in the Advocate (أدفوكيت) project.

---

## 🎨 Color Palette & Tokens

### Primary Brand (Central & Super Admin)
- **Primary Red**: `red-700` (`#b91c1c`), Hover: `red-800` (`#991b1b`)
- **Primary Accent Light**: `red-50` (`#fef2f2`), Border: `red-100` / `red-200`
- **Text Primary**: `stone-900` (`#1c1917`), `stone-800` (`#292524`)
- **Text Secondary**: `stone-500` (`#78716c`), Muted: `stone-400` (`#a8a29e`)
- **Backgrounds**: `white` (`#ffffff`), Surface Light: `stone-50` (`#fafaf9`)
- **Borders**: `stone-200/80` (`#e7e5e4`)

### Tenant Lawyer Portal
- **Primary Blue**: `blue-700` (`#1d4ed8`), Hover: `blue-800` (`#1e40af`)
- **Accent Light**: `blue-50` (`#eff6ff`), Border: `blue-100` / `blue-200`

### Status Badges & Alerts
- **Trial / Warning**: `amber-500` / `amber-50` text: `amber-800`, border: `amber-200`
- **Success / Active**: `emerald-600` / `emerald-50` text: `emerald-700`, border: `emerald-200`
- **Danger / Expired / Delete**: `red-600` / `rose-50` text: `rose-700`, border: `rose-200`

---

## 📐 Layout & Typography Rules

1. **RTL Direction**: All Arabic pages MUST use `dir="rtl"` and class `font-sans`.
2. **Card Surfaces**: Use `bg-white rounded-2xl border border-stone-200/80 p-6 shadow-sm` or `shadow-md`.
3. **Inputs & Form Controls**:
   - `px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all`
4. **Primary Action Buttons**:
   - `px-6 py-3.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl shadow-lg shadow-red-700/15 transition-all duration-300`
5. **No Isolated Dark Theme**: All central pages (landing, registration, admin, login) must follow the light stone & white theme with crisp red accents.
