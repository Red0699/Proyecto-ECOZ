'use strict';

document.addEventListener('DOMContentLoaded', () => {
  if (typeof ApexCharts === 'undefined') return;

  const S = window.homeSeries || { days_all: [] };
  const K = window.homeKpis || {};

  // ========== Utilidades ==========
  const PALETTE = [
    '#1A73E8',
    '#E91E63',
    '#00C853',
    '#FF9800',
    '#8E24AA',
    '#00BCD4',
    '#9C27B0',
    '#43A047',
    '#F4511E',
    '#3F51B5'
  ];
  const toNum = a =>
    (Array.isArray(a) ? a : Object.values(a || {})).map(v => (v == null || v === '' || isNaN(+v) ? null : +v));
  const yFmt = v => (Math.abs(v) < 1e-9 ? '0' : Number(v).toFixed(2));
  const axesFor = cats => ({
    xaxis: { type: 'category', categories: cats, labels: { rotate: 0 } },
    yaxis: { labels: { formatter: yFmt } },
    tooltip: { y: { formatter: yFmt }, x: { formatter: (_, o) => cats?.[o.dataPointIndex] ?? '' } },
    grid: { strokeDashArray: 4 }
  });

  // ====== DEBUG opcional ======
  console.log('[BOOT] homeSeries keys ->', Object.keys(S || {}));
  console.log('[BOOT] homeKpis ->', K);

  // ====== 1) Widget de Tanque (SVG con capacidad) ======
  (function renderTank() {
    const el = document.getElementById('tankWidget');
    if (!el) return;
    const w = el.clientWidth || 140;
    const h = el.clientHeight || 140;
    const r = Math.min(w, h) / 2 - 4;

    const value = Number(K.inv_ult_gl || 0);
    const cap = Math.max(Number(K.inv_capacity_gl || 0), 0.0001);
    const pct = Math.min(1, Math.max(0, value / cap)); // 0–1
    const fillH = 2 * r * pct;
    const lineY = h / 2 - r + 2 * r * (1 - pct);

    el.innerHTML = `
      <svg width="${w}" height="${h}" viewBox="0 0 ${w} ${h}">
        <defs><clipPath id="clipCircle"><circle cx="${w / 2}" cy="${h / 2}" r="${r}"/></clipPath></defs>
        <circle cx="${w / 2}" cy="${h / 2}" r="${r}" fill="white" stroke="#c7c9cc" stroke-width="2"/>
        <g clip-path="url(#clipCircle)">
          <rect x="${w / 2 - r}" y="${h / 2 + r - fillH}" width="${2 * r}" height="${fillH}" fill="#1A73E8" opacity="0.9"/>
        </g>
        <line x1="${w / 2 - r}" x2="${w / 2 + r}" y1="${lineY}" y2="${lineY}" stroke="#0b5ed7" stroke-width="2.5" />
        <line x1="${w / 2}" x2="${w / 2}" y1="${h / 2 - r}" y2="${h / 2 + r}" stroke="#9aa0a6" stroke-dasharray="4 4" stroke-width="1"/>
      </svg>
    `;
  })();

  // ====== 2) COV (kg/día) — todos los días ======
  if (document.querySelector('#covAllDaysChart')) {
    new ApexCharts(document.querySelector('#covAllDaysChart'), {
      chart: { type: 'line', height: 190, toolbar: { show: false } },
      series: [{ name: 'COV (kg/día)', data: toNum(S.cov_all_days) }],
      colors: [PALETTE[4]],
      stroke: { width: 3, curve: 'smooth' },
      markers: { size: 2, colors: [PALETTE[4]], strokeColors: '#fff', strokeWidth: 2 },
      ...axesFor(S.days_all || []),
      dataLabels: { enabled: false }
    }).render();
  }

  // ====== 3) Presión promedio (Psi) — todos los días ======
  if (document.querySelector('#presionAvgDaysChart')) {
    new ApexCharts(document.querySelector('#presionAvgDaysChart'), {
      chart: { type: 'line', height: 170, toolbar: { show: false } },
      series: [{ name: 'Psi (promedio/día)', data: toNum(S.psi_avg_days) }],
      colors: [PALETTE[2]],
      stroke: { width: 3, curve: 'smooth' },
      markers: { size: 2, colors: [PALETTE[2]], strokeColors: '#fff', strokeWidth: 2 },
      ...axesFor(S.days_all || []),
      dataLabels: { enabled: false }
    }).render();
  }

  // ====== 4) Gráfica diaria (último día) — DONUT EDS vs Fórmulas ======
  if (document.querySelector('#homeGraficaDiaria')) {
    const fecha = window.homeKpis?.last_day_str || '—';
    const vEDS = Number(window.homeKpis?.last_day_variacion_eds_gl ?? 0); 
    const vFORM = Number(window.homeKpis?.last_day_variacion_gl ?? 0); // puede ser negativo

    // Datos para el pastel (no admite negativos)
    const absEDS = Math.abs(vEDS);
    const absFORM = Math.abs(vFORM);
    const totalAbs = absEDS + absFORM;

    // Si no hay nada que mostrar, dispara "noData"
    if (totalAbs === 0) {
      new ApexCharts(document.querySelector('#homeGraficaDiaria'), {
        chart: { type: 'donut', height: 260, toolbar: { show: false } },
        series: [],
        labels: [],
        noData: { text: 'Sin datos para el último día', align: 'center' }
      }).render();
    } else {
      // Colores (si EDS es negativo lo pintamos rojo, si no azul; fórmulas en verde)
      const colorEDS = vEDS < 0 ? '#E53935' : '#1A73E8';
      const colorFORM = '#43A047';

      new ApexCharts(document.querySelector('#homeGraficaDiaria'), {
        chart: {
          type: 'donut',
          height: 260,
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        series: [absEDS, absFORM],
        labels: [`Pérdidas totales en EDS (${Math.abs(vEDS).toFixed(2)} gl)`, `Pérdidas por evaporación (${Math.abs(vFORM).toFixed(2)} gl)`],
        colors: [colorEDS, colorFORM],
        dataLabels: {
          enabled: true,
          formatter: val => `${val.toFixed(1)}%`
        },
        legend: {
          position: 'bottom',
          markers: { width: 10, height: 10, radius: 12 }
        },
        tooltip: {
          y: {
            formatter: (val, opts) => {
              // Muestra el valor real con su signo en el tooltip
              const isEds = opts.seriesIndex === 0;
              const real = isEds ? vEDS : vFORM;
              return `${real.toFixed(3)} gl`;
            }
          }
        },
        plotOptions: {
          pie: {
            donut: {
              size: '80%',
              labels: {
                show: true,
                name: { offsetY: 8 },
                value: {
                  formatter: () => `${totalAbs.toFixed(2)} gl`,
                  offsetY: -6
                }
              }
            }
          }
        }
      }).render();
    }
  }
});
