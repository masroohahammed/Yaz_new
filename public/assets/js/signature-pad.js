/**
 * Canvas signature pads — accurate pointer mapping and smooth drawing.
 * Include on pages with .fm-signature-canvas (after DOM ready).
 */
(function () {
  'use strict';

  function getPoint(canvas, e) {
    const rect = canvas.getBoundingClientRect();
    if (rect.width <= 0 || rect.height <= 0) {
      return null;
    }

    let clientX;
    let clientY;
    if (e.touches && e.touches.length) {
      clientX = e.touches[0].clientX;
      clientY = e.touches[0].clientY;
    } else if (e.changedTouches && e.changedTouches.length) {
      clientX = e.changedTouches[0].clientX;
      clientY = e.changedTouches[0].clientY;
    } else {
      clientX = e.clientX;
      clientY = e.clientY;
    }

    return {
      x: clientX - rect.left,
      y: clientY - rect.top,
    };
  }

  function configureContext(ctx) {
    ctx.strokeStyle = '#111';
    ctx.lineWidth = 2.25;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
  }

  function fitCanvas(canvas) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const rect = canvas.getBoundingClientRect();
    const cssWidth = Math.max(rect.width || canvas.offsetWidth || 320, 1);
    const cssHeight = Math.max(rect.height || canvas.offsetHeight || 120, 1);
    const needsResize =
      canvas.width !== Math.round(cssWidth * ratio) ||
      canvas.height !== Math.round(cssHeight * ratio);

    if (! needsResize) {
      return { ctx: canvas.getContext('2d'), cssWidth, cssHeight, ratio };
    }

    const previous = needsResize && canvas.width > 0 ? canvas.toDataURL('image/png') : '';

    canvas.width = Math.round(cssWidth * ratio);
    canvas.height = Math.round(cssHeight * ratio);
    canvas.style.width = cssWidth + 'px';
    canvas.style.height = cssHeight + 'px';

    const ctx = canvas.getContext('2d');
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    configureContext(ctx);

    if (previous && previous.length > 100) {
      const img = new Image();
      img.onload = function () {
        ctx.clearRect(0, 0, cssWidth, cssHeight);
        ctx.drawImage(img, 0, 0, cssWidth, cssHeight);
      };
      img.src = previous;
    }

    return { ctx, cssWidth, cssHeight, ratio };
  }

  function initCanvas(canvas, input) {
    if (! canvas || canvas.dataset.sigInit === '1') {
      return;
    }
    canvas.dataset.sigInit = '1';

    let state = fitCanvas(canvas);
    let drawing = false;
    let lastPoint = null;
    let rafId = 0;
    let pendingPoint = null;
    let hasInk = false;

    function syncInput() {
      if (! input || ! hasInk) {
        if (input) {
          input.value = '';
        }
        return;
      }
      input.value = canvas.toDataURL('image/png');
    }

    function drawSegment(from, to) {
      state.ctx.beginPath();
      state.ctx.moveTo(from.x, from.y);
      state.ctx.lineTo(to.x, to.y);
      state.ctx.stroke();
      hasInk = true;
    }

    function flushPending() {
      rafId = 0;
      if (! drawing || ! lastPoint || ! pendingPoint) {
        return;
      }
      drawSegment(lastPoint, pendingPoint);
      lastPoint = pendingPoint;
      pendingPoint = null;
    }

    function queuePoint(point) {
      pendingPoint = point;
      if (! rafId) {
        rafId = window.requestAnimationFrame(flushPending);
      }
    }

    function pointerDown(e) {
      if (e.pointerType === 'mouse' && e.button !== 0) {
        return;
      }
      e.preventDefault();
      state = fitCanvas(canvas);
      const point = getPoint(canvas, e);
      if (! point) {
        return;
      }
      drawing = true;
      lastPoint = point;
      pendingPoint = null;
      if (rafId) {
        window.cancelAnimationFrame(rafId);
        rafId = 0;
      }
      if (canvas.setPointerCapture && e.pointerId !== undefined) {
        try {
          canvas.setPointerCapture(e.pointerId);
        } catch (err) {
          /* ignore */
        }
      }
    }

    function pointerMove(e) {
      if (! drawing) {
        return;
      }
      e.preventDefault();
      const point = getPoint(canvas, e);
      if (! point || ! lastPoint) {
        return;
      }
      queuePoint(point);
    }

    function pointerUp(e) {
      if (! drawing) {
        return;
      }
      e.preventDefault();
      if (pendingPoint && lastPoint) {
        drawSegment(lastPoint, pendingPoint);
        lastPoint = pendingPoint;
        pendingPoint = null;
      }
      drawing = false;
      if (rafId) {
        window.cancelAnimationFrame(rafId);
        rafId = 0;
      }
      if (canvas.releasePointerCapture && e.pointerId !== undefined) {
        try {
          canvas.releasePointerCapture(e.pointerId);
        } catch (err) {
          /* ignore */
        }
      }
      syncInput();
    }

    canvas.addEventListener('pointerdown', pointerDown);
    canvas.addEventListener('pointermove', pointerMove);
    canvas.addEventListener('pointerup', pointerUp);
    canvas.addEventListener('pointercancel', pointerUp);
    canvas.addEventListener('pointerleave', pointerUp);

    if (typeof ResizeObserver !== 'undefined') {
      const observer = new ResizeObserver(function () {
        if (! drawing) {
          state = fitCanvas(canvas);
        }
      });
      observer.observe(canvas);
    }

    window.addEventListener('orientationchange', function () {
      window.setTimeout(function () {
        state = fitCanvas(canvas);
      }, 120);
    });
  }

  function boot() {
    document.querySelectorAll('.fm-signature-canvas').forEach(function (canvas) {
      const inputId = canvas.id.replace('_canvas', '_input');
      const input = document.getElementById(inputId);
      window.requestAnimationFrame(function () {
        initCanvas(canvas, input);
      });
    });

    document.querySelectorAll('.fm-sig-clear, .sign-pad-clear').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const canvas = document.getElementById(btn.dataset.canvas);
        const input = document.getElementById(btn.dataset.input);
        if (! canvas) {
          return;
        }
        const rect = canvas.getBoundingClientRect();
        const cssWidth = Math.max(rect.width || canvas.offsetWidth || 320, 1);
        const cssHeight = Math.max(rect.height || canvas.offsetHeight || 120, 1);
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, cssWidth, cssHeight);
        if (input) {
          input.value = '';
        }
      });
    });

    document.querySelectorAll('form').forEach(function (form) {
      form.addEventListener('submit', function () {
        form.querySelectorAll('.fm-signature-canvas').forEach(function (canvas) {
          const input = document.getElementById(canvas.id.replace('_canvas', '_input'));
          if (input && ! input.value) {
            const ctx = canvas.getContext('2d');
            const blank = document.createElement('canvas');
            blank.width = canvas.width;
            blank.height = canvas.height;
            if (canvas.toDataURL() !== blank.toDataURL()) {
              input.value = canvas.toDataURL('image/png');
            }
          }
        });
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
