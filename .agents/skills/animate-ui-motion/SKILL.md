---
name: animate-ui-motion
description: >-
  Use when building animated frontend interfaces in React or Vue, upgrading shadcn/ui components with Motion physics, integrating animated primitives (Radix UI, Base UI, React Aria), or adding spring animations, micro-interactions, and visual effects based on Animate UI.
---

# Animate UI & Motion Engineering

Distilled from [Animate UI](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/animate-ui).

## When to Use This Skill
- Building interactive modern web interfaces with fluid spring animations.
- Upgrading existing `shadcn/ui` components to animated versions without rewriting markup or styles.
- Creating micro-interactions: animated tabs, floating cursors, glow effects, spotlight cards, text reveals.
- Integrating with `@animate-ui/react` primitives.

---

## 1. Quick Start & Component Upgrade

### Automated CLI Upgrade (for shadcn/ui projects)
```bash
npx @animate-ui/cli@latest animate all
```

### Manual Primitive Replacement
Keep existing Tailwind styling and DOM structure, only change primitive imports:

```tsx
// Before (standard Radix UI)
import { Accordion as AccordionPrimitive } from 'radix-ui';

// After (Animate UI with built-in spring motion)
import { Accordion as AccordionPrimitive } from '@animate-ui/react/primitives/radix-ui';
```

---

## 2. Core Motion Principles & Physics Guidelines

1. **Spring Physics over Linear Timing**:
   - Never use linear or abrupt cubic-bezier transitions for user-triggered layout changes.
   - Recommended spring values:
     - **Snappy / Micro-interactions (Buttons, Toggles)**: `stiffness: 400, damping: 30`
     - **Smooth / Content Transitions (Modals, Drawers)**: `stiffness: 300, damping: 25`
     - **Gentle / Background Effects (Glow, Floats)**: `stiffness: 120, damping: 20`
2. **Layout Animations (`layout` / `layoutId`)**:
   - Use `layoutId` for shared element transitions (e.g., active tab pill indicator sliding between items).
3. **Reduced Motion Accessibility**:
   - Always respect `prefers-reduced-motion` using Motion's built-in hooks to disable aggressive motion for sensitive users.

---

## 3. Key Primitives Available in Local Repo
Explore local implementations in [packages/react/src/primitives](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/animate-ui/packages/react/src/primitives):
- **`radix-ui/`**: Accordion, Dialog, Dropdown Menu, Popover, Select, Tabs, Tooltip.
- **`buttons/`**: Shimmer buttons, magnetic buttons, bounce buttons.
- **`texts/`**: Typing text, flip words, blur reveal, roll numbers.
- **`effects/`**: Border beam, spotlight card, glow hover.
- **`cursor/`**: Custom following cursor with scale and blend-mode reactions.
