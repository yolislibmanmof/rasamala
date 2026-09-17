<?php
/**
 * Rasamala Background Style definitions.
 *
 * Keep the style keys and labels in one place so TInfo, Theme Viewer, and the
 * runtime resolver expose the same options. The actual visual declarations
 * remain in the theme stylesheet; the custom style is safely injected with a
 * CSP nonce by the header helper.
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

return [
  'none' => [
    'label' => 'None / Standard',
  ],
  'soft-gradient' => [
    'label' => 'Soft Gradient (Theme Colors)',
    'theme_svg' => true,
  ],
  'aurora-glow' => [
    'label' => 'Aurora Glow (Theme Colors)',
    'theme_svg' => true,
  ],
  // Premium Theme-Colored Dynamic SVG Layers
  'image-bg-ocean-waves' => [
    'label' => 'Ocean Waves (Theme Colors)',
    'theme_waves' => true,
    'theme_svg' => true,
  ],
  'image-bg-aurora-neon' => [
    'label' => 'Aurora Neon Borealis (Glow Curtains)',
    'theme_svg' => true,
  ],
  'image-bg-galaxy-nebula' => [
    'label' => 'Galaxy Nebula & Starfield (Cosmic Glow)',
    'theme_svg' => true,
  ],
  'image-bg-quantum-mesh' => [
    'label' => 'Quantum Cyber Mesh (Futuristic Grid)',
    'theme_svg' => true,
  ],
  'image-bg-zen-bamboo' => [
    'label' => 'Zen Bamboo & Drifting Leaves (Nature Harmony)',
    'theme_svg' => true,
  ],
  'image-bg-flying-books-pages' => [
    'label' => 'Floating Books & Scattered Flying Pages',
    'theme_svg' => true,
  ],
  'image-bg-gemini-desk' => [
    'label' => 'Library Desk & Lamp (Gemini Vector 1)',
    'theme_svg' => true,
  ],
  'image-bg-gemini-shelf' => [
    'label' => 'Emerald Shelf & Plants (Gemini Vector 2)',
    'theme_svg' => true,
  ],
  'image-bg-gemini-cosmic' => [
    'label' => 'Cosmic Constellation Books (Gemini Vector 4)',
    'theme_svg' => true,
  ],
  // New reference-driven theme-colored layers. Their visual structure is
  // based on assets/images/backgrounds/new, while CSS variables keep each
  // option synchronized with the active light/dark palette.
  'image-bg-theme-aurora-ribbons' => [
    'label' => 'Aurora Wave Ribbons (Theme Colors)',
    'theme_svg' => true,
    'references' => [
      'assets/images/backgrounds/new/aurora-wave-ribbons-p0qpko.svg',
      'assets/images/backgrounds/new/aurora-wave-ribbons-413125294.svg',
    ],
  ],
  'image-bg-theme-memphis' => [
    'label' => 'Memphis Retro Pattern (Theme Colors)',
    'theme_svg' => true,
    'references' => [
      'assets/images/backgrounds/new/memphis-retro-patterns-xpipzy.svg',
      'assets/images/backgrounds/new/memphis-retro-patterns-472910.svg',
    ],
  ],
  'image-bg-theme-terrazzo' => [
    'label' => 'Terrazzo Speckle (Theme Colors)',
    'theme_svg' => true,
    'references' => ['assets/images/backgrounds/new/terrazzo-speckle-482303512.svg'],
  ],
  'custom' => [
    'label' => 'Custom Background Style',
    'custom' => true,
  ],
];
