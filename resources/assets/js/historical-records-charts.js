// resources/assets/js/historical-records-charts.js
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  if (typeof ApexCharts === 'undefined') return;

  const S = window.series || { labels: [] };
  const F = window.filterMeta || {};

  // Paleta amplia
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
    '#3F51B5',
    '#FDD835',
    '#26A69A',
    '#5C6BC0',
    '#EC407A',
    '#7CB342'
  ];
  const colorsFor = (n, start = 0) => Array.from({ length: n }, (_, i) => PALETTE[(i + start) % PALETTE.length]);

  const labels = Array.isArray(S.labels)
    ? S.labels
    : S.labels && typeof S.labels === 'object'
      ? Object.values(S.labels)
      : [];

  const toNum = arr => {
    if (!arr) return [];
    const vals = Array.isArray(arr) ? arr : typeof arr === 'object' ? Object.values(arr) : [];
    return vals.map(v => (v === null || v === '' || isNaN(Number(v)) ? null : Number(v)));
  };

  const yFmt = v => (Math.abs(v) < 1e-9 ? '0' : Number(v).toFixed(2));
  const noData = { text: 'Sin datos', align: 'center' };

  const axesFor = cats => ({
    xaxis: { type: 'category', categories: cats, labels: { rotate: 0 } },
    yaxis: { labels: { formatter: yFmt } },
    tooltip: {
      theme: 'light',
      x: { formatter: (_, opts) => cats?.[opts.dataPointIndex] ?? '' },
      y: { formatter: yFmt }
    },
    grid: { strokeDashArray: 4 }
  });

  // 1) INVENTARIO: Pie (distribución por período)
  if (document.querySelector('#invChart')) {
    const data = toNum(S.inventario);
    new ApexCharts(document.querySelector('#invChart'), {
      chart: { type: 'pie', height: 340, toolbar: { show: false } },
      series: data,
      labels: labels,
      colors: colorsFor(data.length),
      legend: {
        position: 'right',
        formatter: (val, opts) => `${val}: ${(data[opts.seriesIndex] ?? 0).toFixed(2)} gl`
      },
      dataLabels: {
        enabled: true,
        formatter: val => `${val.toFixed(1)}%`
      },
      noData
    }).render();
  }

  // 2) PRESIÓN: línea
  if (document.querySelector('#presionChart')) {
    new ApexCharts(document.querySelector('#presionChart'), {
      chart: { type: 'line', height: 300, toolbar: { show: false } },
      series: [{ name: 'Psi máximo', data: toNum(S.psi_max) }],
      colors: [PALETTE[3]],
      stroke: { width: 3, curve: 'smooth' },
      markers: { size: 3, colors: [PALETTE[3]], strokeColors: '#fff', strokeWidth: 2 },
      ...axesFor(labels),
      noData,
      dataLabels: { enabled: false }
    }).render();
  }

  // 3) COV: barras distribuidas
  if (document.querySelector('#covChart')) {
    const data = toNum(S.cov_kg);
    new ApexCharts(document.querySelector('#covChart'), {
      chart: { type: 'bar', height: 300, toolbar: { show: false } },
      series: [{ name: 'COV', data }],
      colors: colorsFor(data.length, 5),
      plotOptions: { bar: { distributed: true, borderRadius: 6 } },
      ...axesFor(labels),
      noData,
      dataLabels: { enabled: false }
    }).render();
  }

  // --- NUEVO: Variación/Pérdida de galonaje (columnas agrupadas) ---
  if (document.querySelector('#variacionChart')) {
    const varChart = new ApexCharts(document.querySelector('#variacionChart'), {
      chart: { type: 'bar', height: 280, toolbar: { show: false } },
      series: [
        { name: 'Por Evaporación (gl)', data: S.var_evap_gl || [] },
        { name: 'Total EDS (gl)', data: S.var_total_gl || [] }
      ],
      xaxis: { categories: S.labels || [] },
      plotOptions: { bar: { columnWidth: '45%', endingShape: 'rounded' } },
      dataLabels: { enabled: false },
      legend: { position: 'top' }
    });
    varChart.render();
  }
});
