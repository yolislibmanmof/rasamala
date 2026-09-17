<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: background_layers.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

$bg_animation_enabled = $rasamala_header['background_animation_enabled'] ?? false;
$palette_switcher_show = $rasamala_header['palette_switcher_show'] ?? false;
$bg_animation = $rasamala_header['background_animation'] ?? 'none';
$speed_mult = $rasamala_header['speed_mult'] ?? 1;
?>

<?php if ($bg_animation_enabled || $palette_switcher_show) : ?>
<div id="background-animation-layer"
     class="background-animation-layer hero-animation-layer hero-animation-<?= themeEscape($bg_animation); ?>"
     data-animation="<?= themeEscape($bg_animation); ?>"
     data-speed-multiplier="<?= themeEscape($speed_mult); ?>"
     aria-hidden="true"
     <?= !$bg_animation_enabled ? 'hidden' : ''; ?>></div>
<?php endif; ?>

<!-- Custom Image Background Layer -->
<div id="rasamala-background-image-layer" class="rasamala-background-image-layer" aria-hidden="true"></div>

<!-- A. Soft Gradient Dynamic SVG Theme Layer -->
<div id="rasamala-theme-soft-gradient-layer" class="rasamala-theme-svg-layer rasamala-theme-soft-gradient-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <linearGradient id="rasamalaSoftGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" class="rasamala-soft-grad-stop-1" />
                <stop offset="50%" class="rasamala-soft-grad-stop-2" />
                <stop offset="100%" class="rasamala-soft-grad-stop-3" />
            </linearGradient>
        </defs>
        <rect width="100%" height="100%" fill="url(#rasamalaSoftGrad)" />
    </svg>
</div>

<!-- Theme-colored reference layer: Aurora Wave Ribbons -->
<div id="rasamala-theme-aurora-ribbons-layer" class="rasamala-theme-svg-layer rasamala-theme-aurora-ribbons-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <linearGradient id="rasamalaThemeAuroraBase" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" class="rasamala-theme-aurora-bg-1" />
                <stop offset="58%" class="rasamala-theme-aurora-bg-2" />
                <stop offset="100%" class="rasamala-theme-aurora-bg-3" />
            </linearGradient>
            <filter id="rasamalaThemeAuroraBlur" x="-10%" y="-15%" width="120%" height="130%">
                <feGaussianBlur stdDeviation="18" />
            </filter>
        </defs>
        <rect width="1600" height="900" fill="url(#rasamalaThemeAuroraBase)" />
        <g class="rasamala-theme-aurora-ribbon-glow" filter="url(#rasamalaThemeAuroraBlur)">
            <path d="M-80 640 C250 180 520 210 760 510 S1240 820 1680 230 L1680 390 C1260 900 980 760 720 590 S230 350 -80 800 Z" />
            <path d="M-100 770 C210 430 470 380 690 610 S1180 930 1690 470 L1690 590 C1220 980 930 880 650 710 S230 560 -100 900 Z" />
        </g>
        <path class="rasamala-theme-aurora-ribbon-1" d="M-80 610 C250 130 520 190 760 480 S1240 790 1680 190 L1680 290 C1260 850 990 710 720 550 S240 290 -80 730 Z" />
        <path class="rasamala-theme-aurora-ribbon-2" d="M-100 730 C220 360 470 330 700 560 S1190 880 1690 400 L1690 500 C1220 960 930 820 650 660 S230 490 -100 850 Z" />
        <path class="rasamala-theme-aurora-ribbon-3" d="M-120 820 C200 570 400 530 610 690 S1110 950 1700 650 L1700 730 C1190 1030 920 940 570 790 S210 690 -120 900 Z" />
    </svg>
</div>

<!-- Theme-colored reference layer: Memphis Retro -->
<div id="rasamala-theme-memphis-layer" class="rasamala-theme-svg-layer rasamala-theme-memphis-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" focusable="false">
        <rect width="1200" height="800" class="rasamala-theme-memphis-base" />
        <g class="rasamala-theme-memphis-shapes">
            <polyline points="70,110 100,145 130,110 160,145 190,110" class="rasamala-memphis-zigzag-1" />
            <polyline points="980,120 1010,155 1040,120 1070,155 1100,120" class="rasamala-memphis-zigzag-2" />
            <polyline points="90,650 120,685 150,650 180,685 210,650" class="rasamala-memphis-zigzag-3" />
            <circle cx="290" cy="160" r="52" class="rasamala-memphis-ring-1" />
            <circle cx="930" cy="620" r="70" class="rasamala-memphis-ring-2" />
            <circle cx="1020" cy="300" r="13" class="rasamala-memphis-dot-1" />
            <circle cx="180" cy="360" r="9" class="rasamala-memphis-dot-2" />
            <circle cx="420" cy="235" r="7" class="rasamala-memphis-dot-1" />
            <circle cx="760" cy="520" r="11" class="rasamala-memphis-dot-2" />
            <path d="M525 95 L555 150 L495 150 Z" class="rasamala-memphis-triangle-1" />
            <path d="M650 650 L690 725 L610 725 Z" class="rasamala-memphis-triangle-2" />
            <path d="M305 520 L330 565 L280 565 Z" class="rasamala-memphis-triangle-2" />
            <path d="M1080 520 L1105 565 L1055 565 Z" class="rasamala-memphis-triangle-1" />
            <g class="rasamala-memphis-cross-1"><path d="M430 610 H500 M465 575 V645" /></g>
            <g class="rasamala-memphis-cross-2"><path d="M780 160 H840 M810 130 V190" /></g>
            <g class="rasamala-memphis-cross-1"><path d="M300 280 H350 M325 255 V305" /></g>
            <g class="rasamala-memphis-cross-2"><path d="M1040 700 H1090 M1065 675 V725" /></g>
            <path d="M850 390 C900 350 950 390 990 350" class="rasamala-memphis-arc" />
            <path d="M240 430 C290 390 340 430 390 390" class="rasamala-memphis-arc" />
        </g>
    </svg>
</div>

<!-- Theme-colored reference layer: Isometric 3D Cubes -->
<div id="rasamala-theme-cubes-layer" class="rasamala-theme-svg-layer rasamala-theme-cubes-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 400 225" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <linearGradient id="rasamalaThemeCubeTop" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" class="rasamala-cube-top-1" /><stop offset="100%" class="rasamala-cube-top-2" /></linearGradient>
            <linearGradient id="rasamalaThemeCubeLeft" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" class="rasamala-cube-left-1" /><stop offset="100%" class="rasamala-cube-left-2" /></linearGradient>
            <linearGradient id="rasamalaThemeCubeRight" x1="100%" y1="0%" x2="0%" y2="100%"><stop offset="0%" class="rasamala-cube-right-1" /><stop offset="100%" class="rasamala-cube-right-2" /></linearGradient>
            <g id="rasamalaThemeDenseCube">
                <polygon points="0,0 10.45,-5.225 20.9,0 10.45,5.225" class="rasamala-cube-top" />
                <polygon points="0,0 10.45,5.225 10.45,26.125 0,20.9" class="rasamala-cube-left" />
                <polygon points="20.9,0 10.45,5.225 10.45,26.125 20.9,20.9" class="rasamala-cube-right" />
            </g>
            <g id="rasamalaThemeDenseCubeAlt">
                <polygon points="0,0 10.45,-5.225 20.9,0 10.45,5.225" class="rasamala-cube-top-alt" />
                <polygon points="0,0 10.45,5.225 10.45,26.125 0,20.9" class="rasamala-cube-left-alt" />
                <polygon points="20.9,0 10.45,5.225 10.45,26.125 20.9,20.9" class="rasamala-cube-right-alt" />
            </g>
            <pattern id="rasamalaThemeCubePattern" width="84" height="63" patternUnits="userSpaceOnUse">
                <use href="#rasamalaThemeDenseCube" x="0" y="15" />
                <use href="#rasamalaThemeDenseCubeAlt" x="21" y="4.5" />
                <use href="#rasamalaThemeDenseCube" x="42" y="15" />
                <use href="#rasamalaThemeDenseCubeAlt" x="63" y="4.5" />
                <use href="#rasamalaThemeDenseCubeAlt" x="10.5" y="36" />
                <use href="#rasamalaThemeDenseCube" x="31.5" y="25.5" />
                <use href="#rasamalaThemeDenseCubeAlt" x="52.5" y="36" />
                <use href="#rasamalaThemeDenseCube" x="73.5" y="25.5" />
            </pattern>
        </defs>
        <rect width="400" height="225" class="rasamala-theme-cubes-base" />
        <rect width="400" height="225" fill="url(#rasamalaThemeCubePattern)" />
    </svg>
</div>

<!-- Theme-colored reference layer: Terrazzo Speckle -->
<div id="rasamala-theme-terrazzo-layer" class="rasamala-theme-svg-layer rasamala-theme-terrazzo-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <pattern id="rasamalaThemeTerrazzoPattern" width="420" height="360" patternUnits="userSpaceOnUse">
                <rect width="420" height="360" class="rasamala-terrazzo-pattern-base" />
                <g class="rasamala-terrazzo-speckles">
                    <polygon points="55,70 72,45 92,70 72,95" class="rasamala-terrazzo-1" />
                    <polygon points="190,40 205,25 224,50 204,68" class="rasamala-terrazzo-2" />
                    <polygon points="340,115 370,96 382,125 354,143" class="rasamala-terrazzo-3" />
                    <polygon points="120,180 150,160 166,188 140,210" class="rasamala-terrazzo-4" />
                    <polygon points="282,250 300,220 324,248 304,278" class="rasamala-terrazzo-1" />
                    <polygon points="38,300 66,280 86,310 55,328" class="rasamala-terrazzo-3" />
                    <polygon points="112,118 126,102 140,120 128,136" class="rasamala-terrazzo-2" />
                    <polygon points="224,105 238,88 252,108 238,122" class="rasamala-terrazzo-3" />
                    <polygon points="365,188 380,172 394,193 378,206" class="rasamala-terrazzo-4" />
                    <polygon points="18,170 34,154 48,176 30,190" class="rasamala-terrazzo-1" />
                    <polygon points="92,238 108,220 124,244 106,258" class="rasamala-terrazzo-4" />
                    <polygon points="198,318 214,298 230,320 212,336" class="rasamala-terrazzo-2" />
                    <polygon points="332,320 348,300 364,322 346,340" class="rasamala-terrazzo-1" />
                    <polygon points="262,168 274,150 289,170 276,185" class="rasamala-terrazzo-3" />
                    <polygon points="150,330 162,314 178,332 164,348" class="rasamala-terrazzo-2" />
                    <circle cx="250" cy="142" r="8" class="rasamala-terrazzo-dot-1" />
                    <circle cx="382" cy="290" r="6" class="rasamala-terrazzo-dot-2" />
                    <circle cx="168" cy="306" r="5" class="rasamala-terrazzo-dot-3" />
                    <circle cx="92" cy="142" r="4" class="rasamala-terrazzo-dot-1" />
                    <circle cx="318" cy="58" r="5" class="rasamala-terrazzo-dot-3" />
                    <circle cx="220" cy="228" r="4" class="rasamala-terrazzo-dot-2" />
                </g>
            </pattern>
        </defs>
        <rect width="1200" height="800" fill="url(#rasamalaThemeTerrazzoPattern)" />
    </svg>
</div>

<!-- B. Aurora Glow Dynamic SVG Theme Layer -->
<div id="rasamala-theme-aurora-glow-layer" class="rasamala-theme-svg-layer rasamala-theme-aurora-glow-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <filter id="auroraCoreBlur" x="-30%" y="-30%" width="160%" height="160%">
                <feGaussianBlur stdDeviation="80" />
            </filter>
        </defs>
        <g filter="url(#auroraCoreBlur)">
            <ellipse cx="300" cy="200" rx="400" ry="250" class="rasamala-aurora-core-1" />
            <ellipse cx="900" cy="350" rx="450" ry="300" class="rasamala-aurora-core-2" />
            <ellipse cx="550" cy="650" rx="500" ry="220" class="rasamala-aurora-core-3" />
        </g>
    </svg>
</div>

<!-- 1. Ocean Waves Dynamic SVG Layer -->
<div id="rasamala-theme-wave-layer" class="rasamala-theme-svg-layer rasamala-theme-wave-layer" aria-hidden="true">
    <svg class="rasamala-theme-wave-svg" viewBox="0 0 1440 320" preserveAspectRatio="xMidYMax slice" focusable="false">
        <path class="rasamala-theme-wave-back" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        <path class="rasamala-theme-wave-front" d="M0,256L48,245.3C96,235,192,213,288,192C384,171,480,149,576,165.3C672,181,768,235,864,250.7C960,267,1056,245,1152,224C1248,203,1344,181,1392,170.7L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
    </svg>
</div>

<!-- 2. Liquid Cyber Waves Layer -->
<div id="rasamala-theme-liquid-layer" class="rasamala-theme-svg-layer rasamala-theme-liquid-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <linearGradient id="liquidGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" class="rasamala-grad-stop-primary" />
                <stop offset="100%" class="rasamala-grad-stop-accent" />
            </linearGradient>
            <linearGradient id="liquidGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" class="rasamala-grad-stop-secondary" />
                <stop offset="100%" class="rasamala-grad-stop-primary" />
            </linearGradient>
        </defs>
        <path d="M0,320 C320,500 420,200 720,380 C1020,560 1150,220 1440,360 L1440,900 L0,900 Z" fill="url(#liquidGrad1)" class="rasamala-liquid-back"></path>
        <path d="M0,480 C280,300 520,600 840,420 C1160,240 1280,480 1440,380 L1440,900 L0,900 Z" fill="url(#liquidGrad2)" class="rasamala-liquid-front"></path>
    </svg>
</div>

<!-- 3. Aurora Neon Borealis Layer -->
<div id="rasamala-theme-aurora-layer" class="rasamala-theme-svg-layer rasamala-theme-aurora-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <filter id="auroraBlur" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur stdDeviation="60" />
            </filter>
        </defs>
        <g filter="url(#auroraBlur)">
            <ellipse cx="250" cy="150" rx="350" ry="220" class="rasamala-aurora-glow-1" />
            <ellipse cx="950" cy="280" rx="400" ry="260" class="rasamala-aurora-glow-2" />
            <ellipse cx="600" cy="650" rx="450" ry="200" class="rasamala-aurora-glow-3" />
        </g>
    </svg>
</div>

<!-- 4. Galaxy Nebula & Starfield Layer -->
<div id="rasamala-theme-galaxy-layer" class="rasamala-theme-svg-layer rasamala-theme-galaxy-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <radialGradient id="nebulaCore" cx="50%" cy="40%" r="60%">
                <stop offset="0%" class="rasamala-nebula-stop-1" />
                <stop offset="60%" class="rasamala-nebula-stop-2" />
                <stop offset="100%" class="rasamala-nebula-stop-3" />
            </radialGradient>
        </defs>
        <rect width="100%" height="100%" fill="url(#nebulaCore)" />
        <g class="rasamala-starfield-group">
            <circle cx="120" cy="80" r="2.5" class="rasamala-star-bright" />
            <circle cx="340" cy="220" r="1.8" class="rasamala-star-dim" />
            <circle cx="580" cy="110" r="3.2" class="rasamala-star-bright" />
            <circle cx="820" cy="290" r="2.0" class="rasamala-star-dim" />
            <circle cx="1050" cy="140" r="2.8" class="rasamala-star-bright" />
            <circle cx="210" cy="520" r="2.2" class="rasamala-star-dim" />
            <circle cx="480" cy="680" r="3.0" class="rasamala-star-bright" />
            <circle cx="790" cy="590" r="1.5" class="rasamala-star-dim" />
            <circle cx="980" cy="650" r="2.6" class="rasamala-star-bright" />
        </g>
    </svg>
</div>

<!-- 5. Quantum Cyber Mesh Layer -->
<div id="rasamala-theme-quantum-layer" class="rasamala-theme-svg-layer rasamala-theme-quantum-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" focusable="false">
        <path d="M0,150 Q300,50 600,150 T1200,150 M0,300 Q300,200 600,300 T1200,300 M0,450 Q300,350 600,450 T1200,450 M0,600 Q300,500 600,600 T1200,600" class="rasamala-quantum-grid-h" fill="none"></path>
        <path d="M150,0 Q50,400 150,800 M350,0 Q250,400 350,800 M550,0 Q450,400 550,800 M750,0 Q650,400 750,800 M950,0 Q850,400 950,800" class="rasamala-quantum-grid-v" fill="none"></path>
    </svg>
</div>

<!-- 6. Zen Bamboo & Drifting Leaves Layer -->
<div id="rasamala-theme-bamboo-layer" class="rasamala-theme-svg-layer rasamala-theme-bamboo-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" focusable="false">
        <g class="rasamala-bamboo-group">
            <path d="M120,900 V100 M120,700 H128 M120,500 H128 M120,300 H128" class="rasamala-bamboo-stalk-1" stroke-width="12" stroke-linecap="round" fill="none" />
            <path d="M220,900 V50 M220,750 H230 M220,550 H230 M220,350 H230 M220,150 H230" class="rasamala-bamboo-stalk-2" stroke-width="16" stroke-linecap="round" fill="none" />
            <path d="M1300,900 V150 M1300,720 H1310 M1300,520 H1310 M1300,320 H1310" class="rasamala-bamboo-stalk-1" stroke-width="14" stroke-linecap="round" fill="none" />
            <path d="M220,350 C260,330 290,340 310,370 C280,380 250,370 220,350 Z" class="rasamala-bamboo-leaf-1" />
            <path d="M220,550 C180,530 150,540 130,570 C160,580 190,570 220,550 Z" class="rasamala-bamboo-leaf-2" />
            <path d="M1300,320 C1260,300 1230,310 1210,340 C1240,350 1270,340 1300,320 Z" class="rasamala-bamboo-leaf-1" />
            <path d="M600,200 C630,190 650,200 665,220 C645,228 625,220 600,200 Z" class="rasamala-bamboo-leaf-floating" />
            <path d="M850,420 C880,410 900,420 915,440 C895,448 875,440 850,420 Z" class="rasamala-bamboo-leaf-floating" />
        </g>
    </svg>
</div>

<!-- 7. Sunset Wave Horizons Layer -->
<div id="rasamala-theme-sunset-layer" class="rasamala-theme-svg-layer rasamala-theme-sunset-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1440 500" preserveAspectRatio="xMidYMax slice" focusable="false">
        <defs>
            <linearGradient id="sunsetGrad1" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" class="rasamala-sunset-stop-1" />
                <stop offset="100%" class="rasamala-sunset-stop-2" />
            </linearGradient>
        </defs>
        <path d="M0,200 Q360,120 720,220 T1440,180 V500 H0 Z" fill="url(#sunsetGrad1)" class="rasamala-sunset-dune-1" />
        <path d="M0,280 Q400,220 800,320 T1440,260 V500 H0 Z" class="rasamala-sunset-dune-2" />
        <path d="M0,360 Q440,320 880,400 T1440,340 V500 H0 Z" class="rasamala-sunset-dune-3" />
    </svg>
</div>

<!-- 8. Floating Books & Scattered Flying Pages Layer -->
<div id="rasamala-theme-flying-books-layer" class="rasamala-theme-svg-layer rasamala-theme-flying-books-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" focusable="false">
        <g class="rasamala-flying-books-group">
            <!-- Open Book 1 (Top Left) -->
            <g transform="translate(180, 160) scale(1.2)">
                <path d="M0,0 Q30,-15 60,0 L60,40 Q30,25 0,40 Z" class="rasamala-open-book-page-left" />
                <path d="M60,0 Q90,-15 120,0 L120,40 Q90,25 60,40 Z" class="rasamala-open-book-page-right" />
                <path d="M60,0 V40" class="rasamala-book-spine-line" stroke-width="2" />
            </g>
            <!-- Open Book 2 (Bottom Right) -->
            <g transform="translate(1150, 620) scale(1.4) rotate(-12)">
                <path d="M0,0 Q35,-18 70,0 L70,45 Q35,27 0,45 Z" class="rasamala-open-book-page-left" />
                <path d="M70,0 Q105,-18 140,0 L140,45 Q105,27 70,45 Z" class="rasamala-open-book-page-right" />
                <path d="M70,0 V45" class="rasamala-book-spine-line" stroke-width="2" />
            </g>
            <!-- Open Book 3 (Top Right) -->
            <g transform="translate(1080, 150) scale(0.9) rotate(15)">
                <path d="M0,0 Q25,-12 50,0 L50,35 Q25,23 0,35 Z" class="rasamala-open-book-page-left" />
                <path d="M50,0 Q75,-12 100,0 L100,35 Q75,23 50,35 Z" class="rasamala-open-book-page-right" />
            </g>
            <!-- Scattered Flying Paper Pages -->
            <polygon points="320,120 370,100 380,140 330,160" class="rasamala-flying-page-1" />
            <polygon points="500,280 545,260 560,300 515,320" class="rasamala-flying-page-2" />
            <polygon points="850,180 895,165 910,200 865,215" class="rasamala-flying-page-1" />
            <polygon points="220,480 265,460 280,495 235,515" class="rasamala-flying-page-3" />
            <polygon points="980,450 1025,430 1040,470 995,490" class="rasamala-flying-page-2" />
            <polygon points="720,620 765,600 780,640 735,660" class="rasamala-flying-page-1" />
            <polygon points="410,700 455,680 470,720 425,740" class="rasamala-flying-page-3" />
            <!-- Book Stack (Bottom Left) -->
            <g transform="translate(100, 680)">
                <rect x="0" y="40" width="160" height="22" rx="4" class="rasamala-stack-book-1" />
                <rect x="15" y="18" width="140" height="20" rx="3" class="rasamala-stack-book-2" />
                <rect x="25" y="0" width="120" height="17" rx="3" class="rasamala-stack-book-3" />
            </g>
        </g>
    </svg>
</div>

<!-- 9. Gemini Library Desk & Reading Lamp Vector Layer -->
<div id="rasamala-theme-gemini-desk-layer" class="rasamala-theme-svg-layer rasamala-theme-gemini-desk-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1920 1080" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <radialGradient id="geminiDeskBgGlow" cx="20%" cy="75%" r="70%">
                <stop offset="0%" class="rasamala-gemini-desk-stop-1" />
                <stop offset="50%" class="rasamala-gemini-desk-stop-2" />
                <stop offset="100%" class="rasamala-gemini-desk-stop-3" />
            </radialGradient>
            <radialGradient id="geminiDeskLampGlow" cx="22%" cy="65%" r="30%">
                <stop offset="0%" stop-color="var(--theme-accent, #fbbf24)" stop-opacity="0.22" />
                <stop offset="100%" stop-color="var(--theme-accent, #fbbf24)" stop-opacity="0" />
            </radialGradient>
            <filter id="geminiDeskShadow" x="-20%" y="-20%" width="140%" height="140%">
                <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#000000" flood-opacity="0.6" />
            </filter>
            <filter id="geminiDeskSoftGlow">
                <feGaussianBlur stdDeviation="5" result="coloredBlur"/>
                <feMerge>
                    <feMergeNode in="coloredBlur"/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>
            <linearGradient id="geminiDeskBookBlue" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="var(--theme-secondary, #2563eb)" /><stop offset="100%" stop-color="var(--theme-primary, #1d4ed8)" />
            </linearGradient>
            <linearGradient id="geminiDeskBookAmber" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="var(--theme-accent, #f59e0b)" /><stop offset="100%" stop-color="var(--theme-primary, #d97706)" />
            </linearGradient>
            <linearGradient id="geminiDeskBookEmerald" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="var(--theme-primary, #059669)" /><stop offset="100%" stop-color="var(--theme-secondary, #047857)" />
            </linearGradient>
            <g id="geminiDeskSparkle">
                <path d="M 0,-12 Q 0,0 12,0 Q 0,0 0,12 Q 0,0 -12,0 Q 0,0 0,-12 Z" fill="var(--theme-accent, #fbbf24)" opacity="0.8" />
            </g>
        </defs>

        <rect width="1920" height="1080" fill="url(#geminiDeskBgGlow)" opacity="0.9" />
        <rect width="1920" height="1080" fill="url(#geminiDeskLampGlow)" />

        <g stroke="var(--theme-text)" stroke-opacity="0.03" stroke-width="1">
            <line x1="0" y1="180" x2="1920" y2="180" />
            <line x1="0" y1="540" x2="1920" y2="540" />
            <line x1="0" y1="900" x2="1920" y2="900" />
        </g>

        <use href="#geminiDeskSparkle" x="180" y="320" transform="scale(0.8)" />
        <use href="#geminiDeskSparkle" x="420" y="240" transform="scale(1.2)" filter="url(#geminiDeskSoftGlow)" />
        <use href="#geminiDeskSparkle" x="650" y="380" transform="scale(0.6)" opacity="0.5" />

        <g id="geminiDeskStack" filter="url(#geminiDeskShadow)">
            <ellipse cx="320" cy="1000" rx="280" ry="25" fill="#000000" opacity="0.5" />
            <line x1="0" y1="1000" x2="620" y2="1000" stroke="var(--theme-muted)" stroke-width="3" stroke-linecap="round" opacity="0.5" />

            <g transform="translate(80, 930)">
                <rect x="0" y="0" width="450" height="65" rx="5" fill="url(#geminiDeskBookEmerald)" />
                <rect x="30" y="8" width="390" height="48" fill="var(--theme-surface)" opacity="0.9" />
                <rect x="0" y="0" width="30" height="65" fill="var(--theme-primary)" />
            </g>

            <g transform="translate(130, 870) rotate(-2 200 30)">
                <rect x="0" y="0" width="380" height="58" rx="5" fill="url(#geminiDeskBookAmber)" />
                <rect x="25" y="7" width="330" height="42" fill="var(--theme-surface)" opacity="0.9" />
                <rect x="0" y="0" width="25" height="58" fill="var(--theme-accent)" />
            </g>

            <g transform="translate(170, 818) rotate(1.5 160 25)">
                <rect x="0" y="0" width="320" height="50" rx="4" fill="url(#geminiDeskBookBlue)" />
                <rect x="20" y="6" width="280" height="38" fill="var(--theme-surface)" opacity="0.9" />
            </g>

            <g transform="translate(240, 735)">
                <rect x="0" y="10" width="60" height="70" rx="8" fill="var(--theme-surface)" />
                <rect x="6" y="15" width="48" height="12" fill="var(--theme-muted)" opacity="0.3" />
                <path d="M 60,25 C 78,25 78,60 60,60" fill="none" stroke="var(--theme-surface)" stroke-width="7" stroke-linecap="round" />
            </g>
        </g>
    </svg>
</div>

<!-- 10. Gemini Emerald Plant & Golden Shelf Vector Layer -->
<div id="rasamala-theme-gemini-shelf-layer" class="rasamala-theme-svg-layer rasamala-theme-gemini-shelf-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1920 1080" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <linearGradient id="geminiShelfBgTeal" x1="50%" y1="0%" x2="50%" y2="100%">
                <stop offset="0%" class="rasamala-gemini-shelf-stop-1" />
                <stop offset="60%" class="rasamala-gemini-shelf-stop-2" />
                <stop offset="100%" class="rasamala-gemini-shelf-stop-3" />
            </linearGradient>
            <filter id="geminiShelfSoftGlow">
                <feGaussianBlur stdDeviation="6" result="coloredBlur"/>
                <feMerge>
                    <feMergeNode in="coloredBlur"/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>
            <filter id="geminiShelfDropShadow">
                <feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#000000" flood-opacity="0.5" />
            </filter>
            <linearGradient id="geminiShelfCoverGold" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="var(--theme-accent, #f59e0b)" /><stop offset="100%" stop-color="var(--theme-primary, #b45309)" />
            </linearGradient>
            <linearGradient id="geminiShelfCoverCrimson" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="var(--theme-primary, #f43f5e)" /><stop offset="100%" stop-color="var(--theme-secondary, #9f1239)" />
            </linearGradient>
            <linearGradient id="geminiShelfCoverSage" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="var(--theme-secondary, #10b981)" /><stop offset="100%" stop-color="var(--theme-accent, #047857)" />
            </linearGradient>
            <g id="geminiShelfStarDot">
                <circle cx="0" cy="0" r="2" fill="var(--theme-accent, #34d399)" opacity="0.6" />
            </g>
        </defs>

        <rect width="1920" height="1080" fill="url(#geminiShelfBgTeal)" opacity="0.9" />

        <circle cx="300" cy="300" r="250" fill="var(--theme-primary)" opacity="0.05" filter="url(#geminiShelfSoftGlow)" />
        <circle cx="1600" cy="400" r="300" fill="var(--theme-accent)" opacity="0.05" filter="url(#geminiShelfSoftGlow)" />

        <use href="#geminiShelfStarDot" x="200" y="150" />
        <use href="#geminiShelfStarDot" x="450" y="220" />
        <use href="#geminiShelfStarDot" x="800" y="120" />
        <use href="#geminiShelfStarDot" x="1250" y="180" />
        <use href="#geminiShelfStarDot" x="1680" y="140" />
        <use href="#geminiShelfStarDot" x="1750" y="280" />

        <path d="M 200,150 L 450,220 L 800,120" stroke="var(--theme-primary)" stroke-width="0.8" stroke-dasharray="3 3" opacity="0.2" fill="none" />
        <path d="M 1250,180 L 1680,140 L 1750,280" stroke="var(--theme-accent)" stroke-width="0.8" stroke-dasharray="3 3" opacity="0.2" fill="none" />

        <rect x="0" y="960" width="1920" height="120" fill="var(--theme-background)" opacity="0.8" />
        <line x1="0" y1="960" x2="1920" y2="960" stroke="var(--theme-primary)" stroke-opacity="0.4" stroke-width="3" />

        <g id="geminiBottomShelfElements" filter="url(#geminiShelfDropShadow)">
            <g transform="translate(100, 820)">
                <path d="M 10,80 L 30,140 L 80,140 L 100,80 Z" fill="var(--theme-surface)" />
                <ellipse cx="55" cy="80" rx="45" ry="8" fill="var(--theme-muted)" opacity="0.5" />
                <path d="M 55,75 C 20,40 -10,50 5,20 C 30,30 50,60 55,75 Z" fill="var(--theme-primary)" />
                <path d="M 55,75 C 80,30 120,40 105,10 C 80,20 60,50 55,75 Z" fill="var(--theme-accent)" />
                <path d="M 55,75 C 40,20 50,-10 30,-20 C 15,0 35,40 55,75 Z" fill="var(--theme-secondary)" />

                <g transform="translate(130, 20)">
                    <rect x="0" y="0" width="28" height="120" rx="3" fill="url(#geminiShelfCoverCrimson)" />
                    <rect x="33" y="15" width="22" height="105" rx="3" fill="url(#geminiShelfCoverGold)" />
                    <rect x="60" y="-10" width="34" height="130" rx="3" fill="url(#geminiShelfCoverSage)" />
                    <g transform="translate(100, 10) rotate(14)">
                        <rect x="0" y="0" width="26" height="110" rx="3" fill="var(--theme-primary)" />
                    </g>
                </g>
            </g>

            <g transform="translate(580, 870)">
                <rect x="0" y="55" width="260" height="35" rx="4" fill="var(--theme-surface)" />
                <rect x="15" y="25" width="230" height="30" rx="4" fill="url(#geminiShelfCoverGold)" />
                <rect x="30" y="0" width="200" height="25" rx="3" fill="url(#geminiShelfCoverCrimson)" />
                <circle cx="100" cy="-10" r="10" fill="none" stroke="var(--theme-accent)" stroke-width="2" />
                <circle cx="130" cy="-10" r="10" fill="none" stroke="var(--theme-accent)" stroke-width="2" />
                <line x1="110" y1="-10" x2="120" y2="-10" stroke="var(--theme-accent)" stroke-width="2" />
            </g>

            <g transform="translate(1150, 830)">
                <circle cx="40" cy="70" r="35" fill="var(--theme-surface)" stroke="var(--theme-accent)" stroke-width="3" />
                <line x1="40" y1="70" x2="40" y2="50" stroke="var(--theme-accent)" stroke-width="3" stroke-linecap="round" />
                <line x1="40" y1="70" x2="55" y2="70" stroke="var(--theme-accent)" stroke-width="2.5" stroke-linecap="round" />
                <rect x="90" y="10" width="30" height="120" rx="3" fill="url(#geminiShelfCoverSage)" />
                <rect x="125" y="30" width="24" height="100" rx="3" fill="var(--theme-secondary)" />
                <rect x="154" y="0" width="36" height="130" rx="3" fill="url(#geminiShelfCoverGold)" />
            </g>

            <g transform="translate(1600, 800)">
                <rect x="80" y="130" width="80" height="30" rx="5" fill="var(--theme-surface)" />
                <line x1="120" y1="130" x2="120" y2="40" stroke="var(--theme-muted)" stroke-width="6" />
                <path d="M 90,40 L 150,40 L 165,80 L 75,80 Z" fill="var(--theme-accent)" />
                <polygon points="75,80 165,80 240,160 0,160" fill="var(--theme-accent)" opacity="0.15" />
                <rect x="10" y="125" width="110" height="22" rx="3" fill="url(#geminiShelfCoverCrimson)" />
            </g>
        </g>
    </svg>
</div>

<!-- 11. Gemini Cosmic Constellation & Glowing Books Vector Layer -->
<div id="rasamala-theme-gemini-cosmic-layer" class="rasamala-theme-svg-layer rasamala-theme-gemini-cosmic-layer" aria-hidden="true">
    <svg class="rasamala-theme-full-svg" viewBox="0 0 1920 1080" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <radialGradient id="geminiCosmicBg" cx="50%" cy="50%" r="70%">
                <stop offset="0%" class="rasamala-gemini-cosmic-stop-1" />
                <stop offset="60%" class="rasamala-gemini-cosmic-stop-2" />
                <stop offset="100%" class="rasamala-gemini-cosmic-stop-3" />
            </radialGradient>
            <radialGradient id="geminiCosmicCenterGlow" cx="50%" cy="50%" r="40%">
                <stop offset="0%" stop-color="var(--theme-accent)" stop-opacity="0.15" />
                <stop offset="100%" stop-color="var(--theme-accent)" stop-opacity="0" />
            </radialGradient>
            <filter id="geminiCosmicSoftGlow">
                <feGaussianBlur stdDeviation="6" result="coloredBlur"/>
                <feMerge>
                    <feMergeNode in="coloredBlur"/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>
            <g id="geminiCosmicGlowingBook">
                <path d="M -35,-12 Q 0,-25 35,-12 L 35,18 Q 0,5 -35,18 Z" fill="none" stroke="var(--theme-primary)" stroke-width="2" />
                <path d="M 0,-20 L 0,8" stroke="var(--theme-accent)" stroke-width="1.5" />
            </g>
            <g id="geminiCosmicSparkleStar">
                <path d="M 0,-10 Q 0,0 10,0 Q 0,0 0,10 Q 0,0 -10,0 Q 0,0 0,-10 Z" fill="var(--theme-accent, #fbbf24)" opacity="0.8" />
            </g>
        </defs>

        <rect width="1920" height="1080" fill="url(#geminiCosmicBg)" opacity="0.9" />
        <rect width="1920" height="1080" fill="url(#geminiCosmicCenterGlow)" />
        <rect x="460" y="180" width="1000" height="720" rx="24" fill="none" stroke="var(--theme-primary)" stroke-opacity="0.1" stroke-width="1.5" stroke-dasharray="8 8" />

        <g transform="translate(200, 160)">
            <use href="#geminiCosmicGlowingBook" transform="rotate(-20) scale(1.3)" filter="url(#geminiCosmicSoftGlow)" />
            <use href="#geminiCosmicSparkleStar" x="80" y="-30" transform="scale(1.2)" />
            <circle cx="-40" cy="60" r="3" fill="var(--theme-accent)" opacity="0.7" />
            <path d="M -40,60 L 0,-20 L 80,-30" stroke="var(--theme-primary)" stroke-width="1" stroke-dasharray="3 3" opacity="0.3" fill="none" />
        </g>

        <g transform="translate(1720, 180)">
            <use href="#geminiCosmicGlowingBook" transform="rotate(15) scale(1.4)" filter="url(#geminiCosmicSoftGlow)" />
            <use href="#geminiCosmicSparkleStar" x="-90" y="20" transform="scale(1)" />
            <circle cx="50" cy="-40" r="4" fill="var(--theme-accent)" opacity="0.6" />
            <path d="M -90,20 L 0,0 L 50,-40" stroke="var(--theme-accent)" stroke-width="1" stroke-dasharray="3 3" opacity="0.3" fill="none" />
        </g>

        <g transform="translate(180, 880)">
            <rect x="-80" y="0" width="220" height="35" rx="4" fill="none" stroke="var(--theme-primary)" stroke-width="2" opacity="0.4" />
            <rect x="-60" y="-32" width="180" height="30" rx="4" fill="none" stroke="var(--theme-accent)" stroke-width="2" opacity="0.5" />
            <use href="#geminiCosmicSparkleStar" x="120" y="-20" transform="scale(0.9)" />
        </g>

        <g transform="translate(1700, 860)">
            <circle cx="0" cy="0" r="60" fill="none" stroke="var(--theme-primary)" stroke-width="1.5" opacity="0.3" />
            <circle cx="0" cy="0" r="45" fill="none" stroke="var(--theme-accent)" stroke-width="1" stroke-dasharray="4 4" opacity="0.4" />
            <line x1="-70" y1="0" x2="70" y2="0" stroke="var(--theme-primary)" stroke-width="1" opacity="0.2" />
            <line x1="0" y1="-70" x2="0" y2="70" stroke="var(--theme-primary)" stroke-width="1" opacity="0.2" />
            <use href="#geminiCosmicSparkleStar" x="-40" y="-50" transform="scale(1.1)" />
        </g>

        <path d="M 800,80 Q 960,110 1120,80" fill="none" stroke="var(--theme-primary)" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.25" />
        <circle cx="960" cy="95" r="4" fill="var(--theme-accent)" opacity="0.7" filter="url(#geminiCosmicSoftGlow)" />

        <path d="M 820,1000 Q 960,970 1100,1000" fill="none" stroke="var(--theme-primary)" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.25" />
        <circle cx="960" cy="985" r="4" fill="var(--theme-accent)" opacity="0.7" filter="url(#geminiCosmicSoftGlow)" />
    </svg>
</div>
