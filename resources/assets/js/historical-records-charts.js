'use strict';

document.addEventListener('DOMContentLoaded', () => {
  if (typeof ApexCharts === 'undefined') return;

  const S = window.series || { labels: [] };

  // ====== 1) PALETA AMPLIA ======
  // Puedes editar/añadir los que quieras
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

  // Devuelve N colores ciclando la paleta (para barras distribuidas)
  const colorsFor = (n, start = 0) => Array.from({ length: n }, (_, i) => PALETTE[(i + start) % PALETTE.length]);

  // ====== 2) Normalización de datos ======
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

  // ====== 3) Opciones comunes ======
  const yFmt = v => (Math.abs(v) < 1e-9 ? '0' : Number(v).toFixed(2));
  const noData = { text: 'Sin datos para el rango', align: 'center' };

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

  // ====== INVENTARIO (Pastel de volúmenes diarios) ======
  if (document.querySelector('#invChart')) {
    const data = toNum(S.inventario);
    const cats = labels;

    new ApexCharts(document.querySelector('#invChart'), {
      chart: { type: 'pie', height: 300, toolbar: { show: false } },
      series: data,
      labels: cats,
      colors: colorsFor(data.length, 0), // paleta variada
      legend: {
        position: 'right',
        offsetY: 20,
        formatter: function (val, opts) {
          const value = data[opts.seriesIndex];
          return `${val}: ${value?.toFixed?.(2) ?? 0} gl`;
        }
      },
      tooltip: {
        y: { formatter: val => `${val.toFixed(2)} gl` }
      },
      noData: { text: 'Sin datos para el rango', align: 'center' },
      dataLabels: {
        enabled: true,
        formatter: function (val, opts) {
          return `${val.toFixed(1)}%`;
        },
        dropShadow: { enabled: false }
      }
    }).render();
  }
  // ====== 5) PRESIÓN (línea, otro color) ======
  if (document.querySelector('#presionChart')) {
    new ApexCharts(document.querySelector('#presionChart'), {
      chart: { type: 'line', height: 260, toolbar: { show: false } },
      series: [{ name: 'Psi (máx/día)', data: toNum(S.psi_max) }],
      colors: [PALETTE[2]],
      stroke: { width: 3, curve: 'smooth' },
      markers: { size: 3, colors: [PALETTE[2]], strokeColors: '#fff', strokeWidth: 2 },
      ...axesFor(labels),
      noData,
      dataLabels: { enabled: false }
    }).render();
  }

  // ====== 6) PÉRDIDAS COV (barras distribuidas: cada barra con color) ======
  if (document.querySelector('#perdidasChart')) {
    const data = toNum(S.cov_kg);
    new ApexCharts(document.querySelector('#perdidasChart'), {
      chart: { type: 'bar', height: 260, toolbar: { show: false } },
      series: [{ name: 'COV (kg/día)', data }],
      colors: colorsFor(data.length, 3), // <- paleta variada por barra
      plotOptions: { bar: { distributed: true, borderRadius: 4 } },
      ...axesFor(labels),
      noData,
      dataLabels: { enabled: false }
    }).render();
  }

  // ====== 7) COV (línea, otro color) ======
  if (document.querySelector('#covChart')) {
    new ApexCharts(document.querySelector('#covChart'), {
      chart: { type: 'line', height: 300, toolbar: { show: false } },
      series: [{ name: 'COV (kg/día)', data: toNum(S.cov_kg) }],
      colors: [PALETTE[4]],
      stroke: { width: 3, curve: 'smooth' },
      markers: { size: 3, colors: [PALETTE[4]], strokeColors: '#fff', strokeWidth: 2 },
      ...axesFor(labels),
      noData,
      dataLabels: { enabled: false }
    }).render();
  }

  // ====== 8) VARIACIÓN (barras distribuidas) ======
  if (document.querySelector('#variacionChart')) {
    const data = toNum(S.variacion_gl);
    new ApexCharts(document.querySelector('#variacionChart'), {
      chart: { type: 'bar', height: 260, toolbar: { show: false } },
      series: [{ name: 'Sumatoria variación (gl)', data }],
      colors: colorsFor(data.length, 6), // <- paleta variada por barra
      plotOptions: { bar: { distributed: true, borderRadius: 4 } },
      ...axesFor(labels),
      noData,
      dataLabels: { enabled: false }
    }).render();
  }

  // ====== 9) Ventas/Descargue (stacked, 2 colores distintos) ======
  if (document.querySelector('#graficaDiariaChart')) {
    new ApexCharts(document.querySelector('#graficaDiariaChart'), {
      chart: { type: 'bar', height: 300, stacked: true, toolbar: { show: false } },
      series: [
        { name: 'Ventas (gl)', data: toNum(S.ventas_gl) },
        { name: 'Descargue (gl)', data: toNum(S.descargue_gl) }
      ],
      colors: [PALETTE[1], PALETTE[8]],
      plotOptions: { bar: { borderRadius: 4 } },
      ...axesFor(labels),
      noData,
      dataLabels: { enabled: false },
      legend: { position: 'top' }
    }).render();
  }
});
