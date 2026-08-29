/**
 * Lightweight canvas signature pads — include after DOM ready on pages with .fm-signature-canvas
 */
(function () {
  function initCanvas(canvas, input) {
    if (!canvas || canvas.dataset.sigInit) return;
    canvas.dataset.sigInit = '1';
    const ctx = canvas.getContext('2d');
    const ratio = window.devicePixelRatio || 1;
    const w = canvas.offsetWidth || 400;
    canvas.width = w * ratio;
    canvas.height = 120 * ratio;
    ctx.scale(ratio, ratio);
    ctx.strokeStyle = '#111';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    let drawing = false;

    function pos(e) {
      const r = canvas.getBoundingClientRect();
      const x = (e.touches ? e.touches[0].clientX : e.clientX) - r.left;
      const y = (e.touches ? e.touches[0].clientY : e.clientY) - r.top;
      return { x, y };
    }
    function start(e) { e.preventDefault(); drawing = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }
    function move(e) {
      if (!drawing) return;
      e.preventDefault();
      const p = pos(e);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
    }
    function end() {
      if (!drawing) return;
      drawing = false;
      if (input) input.value = canvas.toDataURL('image/png');
    }
    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end);
  }

  document.querySelectorAll('.fm-signature-canvas').forEach(canvas => {
    const inputId = canvas.id.replace('_canvas', '_input');
    const input = document.getElementById(inputId);
    initCanvas(canvas, input);
  });

  document.querySelectorAll('.fm-sig-clear').forEach(btn => {
    btn.addEventListener('click', () => {
      const canvas = document.getElementById(btn.dataset.canvas);
      const input = document.getElementById(btn.dataset.input);
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      if (input) input.value = '';
    });
  });

  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
      form.querySelectorAll('.fm-signature-canvas').forEach(canvas => {
        const input = document.getElementById(canvas.id.replace('_canvas', '_input'));
        if (input && !input.value) input.value = canvas.toDataURL('image/png');
      });
    });
  });
})();
