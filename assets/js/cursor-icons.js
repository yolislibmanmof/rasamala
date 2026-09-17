/*!
 * Rasamala cursor icon renderer.
 *
 * @Last modified by    : Ade Ismail Siregar (adeismailbox@gmail.com)
 * @Last modified time  : 2026-07-16T15:30:11+07:00
 */
(function () {
  'use strict';

  var MODES = [
    { id: 'neon-comet', label: 'Neon Comet', color: '#22d3ee', accent: '#7c5cff' },
    { id: 'pixel-sword', label: 'Pixel Sword', color: '#a3e635', accent: '#4ade80' },
    { id: 'electric-bolt', label: 'Electric Bolt', color: '#fde047', accent: '#38bdf8' },
    { id: 'ink-brush', label: 'Ink Brush', color: '#f472b6', accent: '#e2e8f0' },
    { id: 'rainbow-ribbon', label: 'Rainbow Ribbon', color: '#f472b6', accent: '#22d3ee' }
  ];
  var LEGACY_MODE_MAP = {
    rocket: 'neon-comet',
    wand: 'electric-bolt',
    book: 'pixel-sword'
  };
  var activeCleanup = null;
  var reducedMotionMedia = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

  function hexToRgb(hex) {
    var c = String(hex || '').replace('#', '');
    if (c.length === 3) c = c.split('').map(function (char) { return char + char; }).join('');
    var n = parseInt(c, 16);
    if (Number.isNaN(n)) return [255, 255, 255];
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
  }

  function cssAccentRgb() {
    var raw = getComputedStyle(document.documentElement).getPropertyValue('--theme-accent-rgb').trim();
    if (!raw) return [111, 91, 67];
    return raw.split(',').map(function (part) { return parseInt(part.trim(), 10) || 0; }).slice(0, 3);
  }

  function rgbString(rgb) {
    return 'rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ')';
  }

  MODES.forEach(function (mode) {
    mode.rgb = hexToRgb(mode.color);
    mode.rgbA = hexToRgb(mode.accent);
  });

  function rgba(rgb, alpha) {
    alpha = Math.max(0, Math.min(1, alpha));
    return 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',' + alpha + ')';
  }

  function findMode(modeId) {
    modeId = LEGACY_MODE_MAP[modeId] || modeId;
    for (var index = 0; index < MODES.length; index += 1) {
      if (MODES[index].id === modeId) return { mode: MODES[index], index: index };
    }
    return { mode: MODES[0], index: 0 };
  }

  function rainbowRgb(t) {
    var h = (((t % 1) + 1) % 1) * 6;
    var i = h | 0;
    var f = h - i;
    var q = 1 - f;
    var r;
    var g;
    var b;

    switch (i % 6) {
      case 0: r = 1; g = f; b = 0; break;
      case 1: r = q; g = 1; b = 0; break;
      case 2: r = 0; g = 1; b = f; break;
      case 3: r = 0; g = q; b = 1; break;
      case 4: r = f; g = 0; b = 1; break;
      default: r = 1; g = 0; b = q;
    }

    return [(r * 255) | 0, (g * 255) | 0, (b * 255) | 0];
  }

  function pointerIsFine() {
    return window.matchMedia && window.matchMedia('(pointer: fine)').matches;
  }

  function reducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function deviceQuality() {
    var cores = navigator.hardwareConcurrency || 4;
    var memory = navigator.deviceMemory || 4;
    var saveData = !!(navigator.connection && navigator.connection.saveData);
    var isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ||
      (navigator.maxTouchPoints > 1 && Math.min(screen.width, screen.height) < 900);

    if (saveData || isMobile || memory <= 2 || cores <= 2) return 0;
    if (memory <= 4 || cores <= 4) return 1;
    return 2;
  }

  function injectStyles() {
    if (document.getElementById('rasamala-cursor-icon-style')) return;

    var style = document.createElement('style');
    style.id = 'rasamala-cursor-icon-style';
    var nonceSource = document.querySelector('style[nonce],script[nonce]');
    var nonce = nonceSource && nonceSource.getAttribute('nonce');
    if (nonce) style.setAttribute('nonce', nonce);
    style.textContent = [
      'body.rasamala-custom-cursor-active,',
      'body.rasamala-custom-cursor-active a,',
      'body.rasamala-custom-cursor-active button,',
      'body.rasamala-custom-cursor-active [role="button"],',
      'body.rasamala-custom-cursor-active label,',
      'body.rasamala-custom-cursor-active .cursor-pointer { cursor: none !important; }',
      'body.rasamala-custom-cursor-active input,',
      'body.rasamala-custom-cursor-active textarea,',
      'body.rasamala-custom-cursor-active select,',
      'body.rasamala-custom-cursor-active iframe { cursor: auto !important; }',
      '#rasamala-cursor-icon-canvas { position: fixed; inset: 0; width: 100%; height: 100%; z-index: 2147483646; pointer-events: none; }'
    ].join('\n');
    document.head.appendChild(style);
  }

  function init() {
    if (typeof activeCleanup === 'function') {
      activeCleanup();
      activeCleanup = null;
    }

    var body = document.body;
    var explicitViewerChoice = body && body.getAttribute('data-cursor-icon-explicit') === '1';
    if (!body || (!explicitViewerChoice && reducedMotion()) || !pointerIsFine()) {
      document.body && document.body.classList.remove('rasamala-custom-cursor-active');
      return;
    }

    var modeId = body.getAttribute('data-cursor-custom-icon') || 'default';
    if (modeId === 'default' || modeId === 'none' || modeId === '0') {
      document.body.classList.remove('rasamala-custom-cursor-active');
      window.RasamalaCursorState = null;
      return;
    }

    // Do not override an explicit Theme Viewer choice when Save-Data is on.
    // The selected icon is already a bounded, lightweight renderer.

    var modeData = findMode(modeId);
    var mode = modeData.mode;
    var modeIndex = modeData.index;
    var themeRgb = cssAccentRgb();
    var canvas = document.createElement('canvas');
    var context = canvas.getContext('2d', { alpha: true, desynchronized: true });
    if (!context) return;
    var width = 0;
    var height = 0;
    var dpr = 1;
    var mouseX = window.innerWidth / 2;
    var mouseY = window.innerHeight / 2;
    var smoothX = mouseX;
    var smoothY = mouseY;
    var velocityX = 0;
    var velocityY = 0;
    var speed = 0;
    var time = 0;
    var angle = 0;
    var visible = true;
    var quality = deviceQuality();
    var maxDpr = quality === 0 ? 1 : quality === 1 ? 1.15 : 1.35;
    var frameInterval = quality === 0 ? 1000 / 30 : quality === 1 ? 1000 / 45 : 1000 / 60;
    var lastFrame = 0;
    var frameId = 0;

    injectStyles();
    canvas.id = 'rasamala-cursor-icon-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    document.body.appendChild(canvas);
    document.body.classList.add('rasamala-custom-cursor-active');

    window.RasamalaCursorModes = MODES;
    window.RasamalaCursorState = {
      modeId: mode.id,
      modeIndex: modeIndex,
      mode: mode,
      x: smoothX,
      y: smoothY,
      speed: speed
    };

    function resize() {
      width = window.innerWidth;
      height = window.innerHeight;
      dpr = Math.min(window.devicePixelRatio || 1, maxDpr);
      canvas.width = Math.max(1, (width * dpr) | 0);
      canvas.height = Math.max(1, (height * dpr) | 0);
      context.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function glow(x, y, radius, rgb, alpha) {
      var gradient = context.createRadialGradient(x, y, 0, x, y, radius);
      gradient.addColorStop(0, rgba(rgb, alpha));
      gradient.addColorStop(1, rgba(rgb, 0));
      context.fillStyle = gradient;
      context.beginPath();
      context.arc(x, y, radius, 0, Math.PI * 2);
      context.fill();
    }

    function currentPalette() {
      if (document.body.classList.contains('rasamala-dark')) {
        return {
          rgb: mode.rgb,
          rgbA: mode.rgbA,
          color: mode.color,
          accent: mode.accent,
          isDark: true
        };
      }

      themeRgb = cssAccentRgb();
      return {
        rgb: themeRgb,
        rgbA: themeRgb,
        color: rgbString(themeRgb),
        accent: rgbString(themeRgb),
        isDark: false
      };
    }

    function drawShape() {
      var x = smoothX;
      var y = smoothY;
      var moveAngle = Math.atan2(velocityY || 0.001, velocityX || 0.001);
      var pulse = 1 + Math.sin(time * 0.006) * 0.07;
      var palette = currentPalette();

      context.save();

      if (mode.id === 'neon-comet') {
        glow(x, y, 28 * pulse, palette.rgb, 0.38);
        context.fillStyle = '#fff';
        context.beginPath();
        context.arc(x, y, 4.5 * pulse, 0, Math.PI * 2);
        context.fill();
        context.strokeStyle = rgba(palette.rgb, 0.9);
        context.lineWidth = 2;
        context.beginPath();
        context.arc(x, y, 10 * pulse, 0, Math.PI * 2);
        context.stroke();
      } else if (mode.id === 'pixel-sword') {
        context.translate(x, y);
        context.rotate(moveAngle + Math.PI / 4);
        context.fillStyle = '#e2e8f0';
        context.fillRect(-2, -24, 4, 20);
        context.fillStyle = palette.color;
        context.fillRect(-2, -4, 4, 8);
        context.fillRect(-6, 0, 12, 4);
        context.fillStyle = '#ca8a04';
        context.fillRect(-2, 8, 4, 12);
      } else if (mode.id === 'electric-bolt') {
        glow(x, y, 26 * pulse, palette.rgb, 0.35);
        context.translate(x, y);
        context.rotate(moveAngle);
        context.strokeStyle = palette.color;
        context.lineWidth = 2.2;
        context.beginPath();
        context.moveTo(-14, 0);
        context.lineTo(-8, -5);
        context.lineTo(-3, 4);
        context.lineTo(4, -4);
        context.lineTo(12, 2);
        context.stroke();
        context.fillStyle = '#fff';
        context.beginPath();
        context.arc(0, 0, 3 * pulse, 0, Math.PI * 2);
        context.fill();
      } else if (mode.id === 'ink-brush') {
        context.translate(x, y);
        context.rotate(moveAngle + Math.PI / 2);
        context.fillStyle = '#1e1b4b';
        context.fillRect(-2.5, -2, 5, 18);
        context.fillStyle = palette.color;
        context.beginPath();
        context.moveTo(-5, -2);
        context.quadraticCurveTo(0, -22 * pulse, 5, -2);
        context.closePath();
        context.fill();
        context.fillStyle = '#f8fafc';
        context.fillRect(-3.5, 16, 7, 5);
      } else if (mode.id === 'rainbow-ribbon') {
        context.translate(x, y);
        for (var i = 0; i < 6; i += 1) {
          var dotColor = palette.isDark ? rainbowRgb(time * 0.0015 + i / 6) : palette.rgb;
          var dotAngle = angle + i * (Math.PI * 2 / 6);
          context.fillStyle = rgba(dotColor, palette.isDark ? 1 : 0.78 - (i * 0.06));
          context.beginPath();
          context.arc(Math.cos(dotAngle) * 7, Math.sin(dotAngle) * 7, 3, 0, Math.PI * 2);
          context.fill();
        }
        context.fillStyle = palette.isDark ? '#fff' : palette.color;
        context.beginPath();
        context.arc(0, 0, 4 * pulse, 0, Math.PI * 2);
        context.fill();
      }

      context.restore();
    }

    function stopFrame() {
      if (frameId) {
        cancelAnimationFrame(frameId);
        frameId = 0;
      }
    }

    function scheduleFrame() {
      if (!visible || document.hidden || frameId) return;
      frameId = requestAnimationFrame(frame);
    }

    function frame(timestamp) {
      frameId = 0;
      if (!visible || document.hidden) return;
      if (timestamp - lastFrame < frameInterval) {
        scheduleFrame();
        return;
      }
      if (timestamp - lastFrame < frameInterval) return;
      lastFrame = timestamp;

      time = timestamp || 0;
      // Keep the decorative glyph close to the real pointer. A low smoothing
      // factor made the icon visibly trail behind fast movements.
      smoothX += (mouseX - smoothX) * 0.78;
      smoothY += (mouseY - smoothY) * 0.78;
      velocityX *= 0.88;
      velocityY *= 0.88;
      speed = Math.hypot(velocityX, velocityY);
      angle += 0.035 + speed * 0.0015;

      window.RasamalaCursorState.x = smoothX;
      window.RasamalaCursorState.y = smoothY;
      window.RasamalaCursorState.speed = speed;

      context.clearRect(0, 0, width, height);
      drawShape();
      scheduleFrame();
    }

    function onMove(event) {
      velocityX = event.clientX - mouseX;
      velocityY = event.clientY - mouseY;
      mouseX = event.clientX;
      mouseY = event.clientY;
    }

    function setVisible(nextVisible) {
      visible = nextVisible;
      canvas.style.opacity = visible ? '1' : '0';
      if (visible) {
        scheduleFrame();
      } else {
        stopFrame();
      }
    }

    function onMouseLeave() { setVisible(false); }
    function onMouseEnter() { setVisible(true); }
    function onVisibilityChange() { setVisible(!document.hidden); }

    window.addEventListener('resize', resize, { passive: true });
    document.addEventListener('mousemove', onMove, { passive: true });
    document.addEventListener('mouseleave', onMouseLeave, { passive: true });
    document.addEventListener('mouseenter', onMouseEnter, { passive: true });
    document.addEventListener('visibilitychange', onVisibilityChange);

    resize();
    scheduleFrame();
    activeCleanup = function () {
      stopFrame();
      window.removeEventListener('resize', resize);
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseleave', onMouseLeave);
      document.removeEventListener('mouseenter', onMouseEnter);
      document.removeEventListener('visibilitychange', onVisibilityChange);
      canvas.remove();
      document.body.classList.remove('rasamala-custom-cursor-active');
      window.RasamalaCursorState = null;
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
  document.addEventListener('rasamala:cursor-settings-changed', init);
  if (reducedMotionMedia) {
    if (reducedMotionMedia.addEventListener) {
      reducedMotionMedia.addEventListener('change', init);
    } else if (reducedMotionMedia.addListener) {
      reducedMotionMedia.addListener(init);
    }
  }
}());
