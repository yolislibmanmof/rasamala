/*!
 * Rasamala cursor trail and particle renderer.
 *
 * @Last modified by    : Ade Ismail Siregar (adeismailbox@gmail.com)
 * @Last modified time  : 2026-07-16T15:30:11+07:00
 */
(function () {
  'use strict';

  var LEGACY_MODE_MAP = {
    rocket: 'neon-comet',
    wand: 'electric-bolt',
    book: 'pixel-sword'
  };
  var activeCleanup = null;
  var reducedMotionMedia = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

  function pointerIsFine() {
    return window.matchMedia && window.matchMedia('(pointer: fine)').matches;
  }

  function reducedMotion() {
    return reducedMotionMedia && reducedMotionMedia.matches;
  }

  function hexToRgb(hex) {
    var c = String(hex || '').replace('#', '');
    if (c.length === 3) c = c.split('').map(function (char) { return char + char; }).join('');
    var n = parseInt(c, 16);
    if (Number.isNaN(n)) return [111, 91, 67];
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
  }

  function cssAccentRgb() {
    var raw = getComputedStyle(document.documentElement).getPropertyValue('--theme-accent-rgb').trim();
    if (!raw) return [111, 91, 67];
    return raw.split(',').map(function (part) { return parseInt(part.trim(), 10) || 0; }).slice(0, 3);
  }

  function rgba(rgb, alpha) {
    alpha = Math.max(0, Math.min(1, alpha));
    return 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',' + alpha + ')';
  }

  function modeMeta(modeId) {
    modeId = LEGACY_MODE_MAP[modeId] || modeId;
    var modes = window.RasamalaCursorModes || [];
    for (var index = 0; index < modes.length; index += 1) {
      if (modes[index].id === modeId) return modes[index];
    }
    return {
      id: 'theme-accent',
      color: getComputedStyle(document.documentElement).getPropertyValue('--theme-accent-color').trim() || '#6f5b43',
      accent: '#ffffff'
    };
  }

  function qualityFromSetting(setting) {
    var cores = navigator.hardwareConcurrency || 4;
    var memory = navigator.deviceMemory || 4;
    var isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ||
      (navigator.maxTouchPoints > 1 && Math.min(screen.width, screen.height) < 900);
    var saveData = !!(navigator.connection && navigator.connection.saveData);

    if (setting === 'low') return 0;
    if (setting === 'medium') return 1;
    if (setting === 'high') return 2;
    if (saveData || memory <= 2 || cores <= 2 || isMobile) return 0;
    if (memory <= 4 || cores <= 4) return 1;
    return 2;
  }

  function injectStyles() {
    if (document.getElementById('rasamala-cursor-particles-style')) return;
    var style = document.createElement('style');
    style.id = 'rasamala-cursor-particles-style';
    var nonceSource = document.querySelector('style[nonce],script[nonce]');
    var nonce = nonceSource && nonceSource.getAttribute('nonce');
    if (nonce) style.setAttribute('nonce', nonce);
    style.textContent = '#rasamala-cursor-particles-canvas{position:fixed;inset:0;width:100%;height:100%;z-index:2147483645;pointer-events:none;}';
    document.head.appendChild(style);
  }

  function init() {
    if (typeof activeCleanup === 'function') {
      activeCleanup();
      activeCleanup = null;
    }

    var body = document.body;
    var explicitViewerChoice = body && body.getAttribute('data-cursor-particles-explicit') === '1';
    if (!body || (!explicitViewerChoice && reducedMotion()) || !pointerIsFine()) return;
    var particleSetting = body.getAttribute('data-cursor-particles') || 'none';
    if (particleSetting === 'none' || particleSetting === '0') return;

    if (particleSetting === 'stars' || particleSetting === 'bubbles' || particleSetting === 'hearts') {
      particleSetting = 'auto';
    }

    var mode = modeMeta(body.getAttribute('data-cursor-custom-icon') || 'theme-accent');
    var baseModeRgb = mode.rgb || hexToRgb(mode.color);
    var baseAccentRgb = mode.rgbA || hexToRgb(mode.accent);
    var themeRgb = cssAccentRgb();
    var quality = qualityFromSetting(particleSetting);
    var caps = [
      { dpr: 1, maxParticles: 45, maxTrail: 14, spawnScale: 0.32, decay: 0.05, fps: 30 },
      { dpr: 1.2, maxParticles: 85, maxTrail: 24, spawnScale: 0.58, decay: 0.04, fps: 45 },
      { dpr: 1.35, maxParticles: 130, maxTrail: 36, spawnScale: 0.82, decay: 0.032, fps: 60 }
    ];
    var q = caps[quality];
    var canvas = document.createElement('canvas');
    var context = canvas.getContext('2d', { alpha: true, desynchronized: true });
    if (!context) return;
    var width = 0;
    var height = 0;
    var dpr = 1;
    var mouseX = window.innerWidth / 2;
    var mouseY = window.innerHeight / 2;
    var lastX = mouseX;
    var lastY = mouseY;
    var speed = 0;
    var visible = true;
    var lastFrame = 0;
    var frameId = 0;
    var trail = [];
    var particles = [];
    var trailPool = [];
    var particlePool = [];

    injectStyles();
    canvas.id = 'rasamala-cursor-particles-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    canvas.setAttribute('role', 'presentation');
    document.body.appendChild(canvas);

    function acquireTrail(x, y) {
      var item = trailPool.pop() || {};
      item.x = x;
      item.y = y;
      item.life = 1;
      return item;
    }

    function releaseTrail(item) {
      trailPool.push(item);
    }

    function acquireParticle() {
      return particlePool.pop() || {};
    }

    function releaseParticle(item) {
      particlePool.push(item);
    }

    function resize() {
      width = window.innerWidth;
      height = window.innerHeight;
      dpr = Math.min(window.devicePixelRatio || 1, q.dpr);
      canvas.width = Math.max(1, (width * dpr) | 0);
      canvas.height = Math.max(1, (height * dpr) | 0);
      context.setTransform(dpr, 0, 0, dpr, 0, 0);
      context.imageSmoothingEnabled = quality > 0;
    }

    function currentPalette() {
      if (document.body.classList.contains('rasamala-dark')) {
        return {
          rgb: baseModeRgb,
          rgbA: baseAccentRgb,
          isDark: true
        };
      }

      themeRgb = cssAccentRgb();
      return {
        rgb: themeRgb,
        rgbA: themeRgb,
        isDark: false
      };
    }

    function pushTrail(x, y) {
      var last = trail[trail.length - 1];
      var minDistance = quality === 0 ? 12 : quality === 1 ? 9 : 7;
      if (last) {
        var dx = x - last.x;
        var dy = y - last.y;
        if (dx * dx + dy * dy < minDistance * minDistance) return;
      }

      trail.push(acquireTrail(x, y));
      while (trail.length > q.maxTrail) releaseTrail(trail.shift());
    }

    function cursorAnchor(event) {
      var state = window.RasamalaCursorState;
      if (state && typeof state.x === 'number' && typeof state.y === 'number') {
        return { x: state.x, y: state.y };
      }

      return { x: event.clientX, y: event.clientY };
    }

    function spawn(count, options) {
      count = Math.max(0, Math.round(count * q.spawnScale));
      var room = q.maxParticles - particles.length;
      if (room <= 0 || count <= 0) return;
      count = Math.min(count, room);
      var palette = currentPalette();

      for (var index = 0; index < count; index += 1) {
        var angle = options.angle != null ? options.angle + (Math.random() - 0.5) * (options.spread || 1) : Math.random() * Math.PI * 2;
        var speedMin = options.speedMin || 0.35;
        var speedMax = options.speedMax || 2.4;
        var particleSpeed = speedMin + Math.random() * (speedMax - speedMin);
        var particle = acquireParticle();
        particle.x = options.x;
        particle.y = options.y;
        particle.vx = Math.cos(angle) * particleSpeed;
        particle.vy = Math.sin(angle) * particleSpeed;
        particle.life = 1;
        particle.decay = options.decay || 0.03;
        particle.size = (options.sizeMin || 1.5) + Math.random() * ((options.sizeMax || 4) - (options.sizeMin || 1.5));
        particle.type = options.type || 'circle';
        particle.rgb = Math.random() > 0.45 ? palette.rgb : palette.rgbA;
        particle.gravity = options.gravity || 0;
        particle.friction = options.friction || 0.96;
        particle.rot = Math.random() * Math.PI * 2;
        particle.vr = (Math.random() - 0.5) * 0.18;
        particles.push(particle);
      }
    }

    function onMove(event) {
      var anchor = cursorAnchor(event);
      var dx = anchor.x - lastX;
      var dy = anchor.y - lastY;
      speed = Math.hypot(dx, dy);
      lastX = anchor.x;
      lastY = anchor.y;
      mouseX = anchor.x;
      mouseY = anchor.y;
      pushTrail(mouseX, mouseY);

      if (speed < 1) return;
      var angle = Math.atan2(dy, dx) + Math.PI;
      var rate = Math.min(4, speed * 0.12);
      var type = 'circle';
      var gravity = 0;

      if (mode.id === 'pixel-sword') type = 'pixel';
      if (mode.id === 'electric-bolt') type = 'spark';

      spawn(rate, {
        x: mouseX,
        y: mouseY,
        angle: angle,
        spread: mode.id === 'ink-brush' ? 0.35 : 0.85,
        speedMin: 0.25,
        speedMax: 1.7 + speed * 0.04,
        sizeMin: 1.5,
        sizeMax: 4.8,
        decay: 0.032,
        type: type,
        gravity: gravity
      });
    }

    function onClick(event) {
      var anchor = cursorAnchor(event);
      spawn(quality === 0 ? 8 : quality === 1 ? 14 : 20, {
        x: anchor.x,
        y: anchor.y,
        speedMin: 1.4,
        speedMax: 5,
        sizeMin: 2,
        sizeMax: 5,
        decay: 0.026,
        type: mode.id === 'electric-bolt' ? 'spark' : 'circle'
      });
    }

    function drawTrail() {
      if (trail.length < 2) return;
      var palette = currentPalette();

      context.lineCap = 'round';
      context.lineJoin = 'round';

      if (quality > 0 && mode.id !== 'pixel-sword') {
        context.beginPath();
        context.moveTo(trail[0].x, trail[0].y);
        for (var index = 1; index < trail.length; index += 1) {
          context.lineTo(trail[index].x, trail[index].y);
        }
        context.strokeStyle = rgba(palette.rgb, palette.isDark ? 0.12 : 0.14);
        context.lineWidth = mode.id === 'rainbow-ribbon' ? 10 : 8;
        context.stroke();
      }

      context.beginPath();
      context.moveTo(trail[0].x, trail[0].y);
      for (var i = 1; i < trail.length; i += 1) {
        context.lineTo(trail[i].x, trail[i].y);
      }
      context.strokeStyle = rgba(palette.rgb, 0.65);
      context.lineWidth = mode.id === 'electric-bolt' ? 2 : 3;
      context.stroke();
    }

    function drawParticles() {
      for (var index = particles.length - 1; index >= 0; index -= 1) {
        var p = particles[index];
        p.x += p.vx;
        p.y += p.vy;
        p.vx *= p.friction;
        p.vy *= p.friction;
        p.vy += p.gravity;
        p.rot += p.vr;
        p.life -= p.decay;

        if (p.life <= 0) {
          releaseParticle(particles[index]);
          particles[index] = particles[particles.length - 1];
          particles.pop();
          continue;
        }

        context.save();
        context.translate(p.x, p.y);
        context.rotate(p.rot);
        context.globalAlpha = p.life;
        context.fillStyle = rgba(p.rgb, p.life);
        context.strokeStyle = rgba(p.rgb, p.life);

        if (p.type === 'pixel') {
          context.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
        } else if (p.type === 'spark') {
          context.lineWidth = 1.4;
          context.beginPath();
          context.moveTo(-p.size, 0);
          context.lineTo(p.size, 0);
          context.moveTo(0, -p.size);
          context.lineTo(0, p.size);
          context.stroke();
        } else {
          context.beginPath();
          context.arc(0, 0, Math.max(0.8, p.size * 0.45), 0, Math.PI * 2);
          context.fill();
        }

        context.restore();
      }
    }

    function updateTrailLife() {
      for (var index = trail.length - 1; index >= 0; index -= 1) {
        trail[index].life -= q.decay;
        if (trail[index].life <= 0) {
          releaseTrail(trail[index]);
          trail.splice(index, 1);
        }
      }
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
      if (timestamp - lastFrame < 1000 / q.fps) {
        scheduleFrame();
        return;
      }
      lastFrame = timestamp;

      context.clearRect(0, 0, width, height);
      drawTrail();
      drawParticles();
      updateTrailLife();
      scheduleFrame();
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

    function onMouseLeave() {
      setVisible(false);
    }

    function onMouseEnter() {
      setVisible(true);
    }

    function onVisibilityChange() {
      setVisible(!document.hidden);
    }

    window.addEventListener('resize', resize, { passive: true });
    document.addEventListener('mousemove', onMove, { passive: true });
    document.addEventListener('click', onClick, { passive: true });
    document.addEventListener('mouseleave', onMouseLeave, { passive: true });
    document.addEventListener('mouseenter', onMouseEnter, { passive: true });
    document.addEventListener('visibilitychange', onVisibilityChange);

    activeCleanup = function () {
      stopFrame();
      window.removeEventListener('resize', resize);
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('click', onClick);
      document.removeEventListener('mouseleave', onMouseLeave);
      document.removeEventListener('mouseenter', onMouseEnter);
      document.removeEventListener('visibilitychange', onVisibilityChange);
      if (canvas && canvas.parentNode) {
        canvas.parentNode.removeChild(canvas);
      }
      trail = [];
      particles = [];
      trailPool = [];
      particlePool = [];
    };

    resize();
    scheduleFrame();
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
