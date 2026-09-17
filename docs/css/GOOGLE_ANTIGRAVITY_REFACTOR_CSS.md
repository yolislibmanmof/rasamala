# Prompt Google Antigravity Agent — Refactor dan Split CSS Rasamala Theme

## Role
You are an AI agent running in Google Antigravity. Your task is to refactor a large SLiMS 9 Bulian OPAC theme stylesheet named Rasamala.

## Primary goal
Refactor the current stylesheet without significantly changing the core visual design, then split it into exactly 3 CSS files:
- `01-core.css`
- `02-opac-pages.css`
- `03-enhancements.css`

## Project context
- The current stylesheet is too large and mixes design tokens, global overrides, OPAC page layout, member/news/detail styling, and heavy decorative effects.
- The current file contains broad global overrides such as `.card`, `.dropdown-menu`, `.modal-content`, `.list-group-item`, `.text-primary`, `.text-muted`, `.bg-primary`, headings, links, and other Bootstrap utility classes.
- The current file also uses several `transition: all` declarations, many large shadows, and several `backdrop-filter`/blur effects on the header, modal, advanced search panel, footer, visitor card, prayer toast, and other decorative elements.
- The theme includes optional/decorative features such as hero animation layers, latest-content ticker, palette switcher, floating controls, WhatsApp/info modal, prayer reminder toast, mobile bottom nav, and background animation mode.
- In a SLiMS workflow, static CSS assets are commonly split into separate files and loaded in sequence.

## Required file split
### 1) `01-core.css`
Include only the theme foundation that must be active on every page:
- `:root` design tokens.
- Bootstrap variable bindings.
- Base typography.
- Body, headings, links, form controls, focus states, border radius tokens, z-index tokens.
- Base button system.
- Base dropdown, base modal, base pagination, base scrollbar.
- Global dark/light contrast guard rules that are truly shared.
- Core `.rasamala-theme` namespace and only the global overrides that are genuinely necessary, scoped as tightly as possible.

Do not place hero animations, tickers, floating widgets, prayer toast, WhatsApp modal, or optional decorative effects in this file.

### 2) `02-opac-pages.css`
Include all styling directly tied to OPAC page behavior:
- Main OPAC layout.
- Search bar and advanced search.
- Search results, filter panel, sort chip, grid/list item.
- Detail record, availability block, author chips, cover block.
- Functional homepage sections.
- News page.
- Librarian page.
- Member login and member dashboard.
- Responsive rules specific to these OPAC pages.

This should be the main file for user-facing catalog pages.

### 3) `03-enhancements.css`
Include all decorative, optional, or performance-heavy features:
- Hero/search banner animation.
- Background animation mode.
- Latest-content ticker/fade slider.
- Palette switcher.
- Floating action buttons/panels.
- Prayer reminder toast.
- WhatsApp/info modal and decorative modal polish.
- Mobile bottom nav.
- Other non-essential visual effects.

This file must be safe to disable without breaking core OPAC functionality.

## Mandatory refactor work
Do all of the following:

1. Replace every `transition: all` with explicit property-based transitions only, such as `transform`, `opacity`, `color`, `background-color`, `border-color`, and `box-shadow`, depending on the component.
2. Tighten global selectors. Avoid broad Bootstrap/SLiMS overrides when they can be moved into `.rasamala-theme ...` or into more specific component selectors.
3. Reduce style leakage from global selectors such as `.card`, `.btn`, `.text-primary`, `.text-muted`, `.bg-primary`, `.modal-content`, and other utility selectors.
4. Standardize repeated component patterns where practical, but do not force unnecessary HTML markup changes.
5. Limit `backdrop-filter` usage to high-value areas only. Keep it only where it is visually important, such as the header or select modals.
6. Simplify the shadow system. Use a small number of consistent shadow levels instead of many large variations.
7. Ensure heavy decorative features live in `03-enhancements.css`, not in core.
8. Preserve visual compatibility for search results, bibliographic detail, member area, news, header, footer, and homepage.
9. Keep mobile behavior safe, especially for dropdowns, navbar collapse, bottom nav, availability popovers, and member/news layouts.
10. Do not remove features without a replacement path. If a heavy feature is retained, move it to enhancements and note it briefly.

## Implementation rules
- Do not change HTML, PHP, or JS unless it is truly necessary to fix CSS import/load order.
- If there is a single legacy import, update it to load the 3 new files in this exact order: `01-core.css`, then `02-opac-pages.css`, then `03-enhancements.css`.
- Preserve existing class names used by the current markup as much as possible.
- Do not perform a major redesign; focus on structure, maintainability, and performance.
- Respect existing SLiMS 9 Bulian behavior.
- Avoid duplicated rules across files.
- If a rule is global, place it in core; if page-contextual, place it in opac-pages; if optional/decorative/performance-heavy, place it in enhancements.
- When in doubt, choose the narrowest-scope file.

## Required output format
Perform the work and return the final result in this format:

1. Change summary.
2. Final file tree.
3. Full contents of the 3 CSS files.
4. The small import/load-order section that must be updated.
5. A short note listing which rules/features were moved to `03-enhancements.css` for performance reasons.

## Completion criteria
The task is complete only if:
- the stylesheet is split into exactly 3 files;
- every rule is placed in the correct file;
- all `transition: all` declarations are removed;
- broad global overrides are narrowed;
- heavy decorative features are isolated in enhancements;
- the result is realistic and directly usable in a SLiMS project.

## Working instruction
Start by grouping the old rules into 3 buckets, then rewrite the final contents of all files in full. Do not output pseudo-code. Return production-ready copy-pasteable CSS.
