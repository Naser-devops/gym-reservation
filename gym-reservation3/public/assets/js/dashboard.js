/* Admin dashboard chart. Data comes from the PHP-rendered data-values attribute (real DB counts). */
(function () {
  'use strict';
  var canvas = document.getElementById('reservationChart');
  if (!canvas || typeof window.Chart === 'undefined') return;
  var vals;
  try { vals = JSON.parse(canvas.getAttribute('data-values') || '[]'); } catch (e) { return; }
  if (!Array.isArray(vals) || vals.length !== 4) return;
  var Chart = window.Chart;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var labels = ['Pending', 'Confirmed', 'Rejected', 'Cancelled'];
  var colors = ['#ffb020', '#3ddc84', '#ff4d5e', '#8b94a1'];
  var total = vals.reduce(function (a, b) { return a + (Number(b) || 0); }, 0);
  var empty = total === 0;

  var centerText = {
    id: 'centerText',
    afterDraw: function (chart) {
      var a = chart.chartArea; if (!a) return;
      var ctx = chart.ctx, cx = (a.left + a.right) / 2, cy = (a.top + a.bottom) / 2;
      ctx.save(); ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
      ctx.fillStyle = '#f4f6f8'; ctx.font = '700 44px "Barlow Condensed", "Arial Narrow", sans-serif'; ctx.fillText(String(total), cx, cy - 8);
      ctx.fillStyle = '#9ba4af'; ctx.font = '600 11px Inter, system-ui, sans-serif'; ctx.fillText('RESERVATIONS', cx, cy + 22);
      ctx.restore();
    }
  };

  new Chart(canvas, {
    type: 'doughnut',
    data: { labels: labels, datasets: [{ data: empty ? [1] : vals, backgroundColor: empty ? ['#1e232c'] : colors, borderColor: '#101318', borderWidth: 4, hoverOffset: empty ? 0 : 8 }] },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '72%',
      animation: reduce ? false : { duration: 1200, easing: 'easeOutQuart', animateRotate: true },
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: !empty, backgroundColor: '#161a21', borderColor: 'rgba(255,255,255,.16)', borderWidth: 1,
          titleColor: '#f4f6f8', bodyColor: '#c4cbd3', padding: 12, cornerRadius: 10, displayColors: true, boxPadding: 4,
          callbacks: { label: function (c) { var v = c.parsed; return ' ' + v + ' (' + Math.round(v / total * 100) + '%)'; } }
        }
      }
    },
    plugins: [centerText]
  });
})();
