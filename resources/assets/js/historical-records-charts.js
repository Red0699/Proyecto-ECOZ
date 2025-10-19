'use strict';

document.addEventListener('DOMContentLoaded', () => {
  if (typeof ApexCharts === 'undefined') return;

  const S = window.series || { labels: [] };

  // === convierto labels a array (por si vienen como objeto) ===
  const labels = Array.isArray(S.labels)
    ? S.labels
    : (S.labels && typeof S.labels === 'object' ? Object.values(S.labels) : []);

  // === convierte cualquier array/objeto a números (null si no es convertible) ===
  const toNum = (arr) => {
    if (!arr) return [];
    const vals = Array.isArray(arr) ? arr : (typeof arr === 'object' ? Object.values(arr) : []);
    return vals.map(v => (v === null || v === '' || isNaN(Number(v)) ? null : Number(v)));
  };

  // === formato de ejes y tooltip ===
  const yFmt = (v) => (Math.abs(v) < 1e-9 ? '0' : Number(v).toFixed(2));
  const xCommon = { type: 'category', categories: labels, labels: { rotate: 0 } };
  const yCommon = { labels: { formatter: yFmt } };
  const tipCommon = {
    y: { formatter: yFmt },
    x: { formatter: (val, opts) => labels[opts.dataPointIndex] || '' }
  };
  const noData = { text: 'Sin datos para el rango', align: 'center' };

  // === INVENTARIO ===
  if (document.querySelector('#invChart')) {
    new ApexCharts(document.querySelector('#invChart'), {
      chart: { type: 'line', height: 240, toolbar: { show: false } },
      series: [{ name: 'Volumen (gl)', data: toNum(S.inventario) }],
      xaxis: xCommon, yaxis: yCommon, tooltip: tipCommon, noData,
      dataLabels: { enabled: false },
      stroke: { width: 3, curve: 'smooth' },
      grid: { strokeDashArray: 4 }
    }).render();
  }

  // === PRESIÓN ===
  if (document.querySelector('#presionChart')) {
    new ApexCharts(document.querySelector('#presionChart'), {
      chart: { type: 'line', height: 260, toolbar: { show: false } },
      series: [{ name: 'Psi (máx/día)', data: toNum(S.psi_max) }],
      xaxis: xCommon, yaxis: yCommon, tooltip: tipCommon, noData,
      dataLabels: { enabled: false },
      stroke: { width: 3, curve: 'smooth' },
      grid: { strokeDashArray: 4 }
    }).render();
  }

  // === COV (barras) ===
  if (document.querySelector('#perdidasChart')) {
    new ApexCharts(document.querySelector('#perdidasChart'), {
      chart: { type: 'bar', height: 260, toolbar: { show: false } },
      series: [{ name: 'COV (kg/día)', data: toNum(S.cov_kg) }],
      xaxis: xCommon, yaxis: yCommon, tooltip: tipCommon, noData,
      dataLabels: { enabled: false },
      grid: { strokeDashArray: 4 }
    }).render();
  }

  // === COV (línea) ===
  if (document.querySelector('#covChart')) {
    new ApexCharts(document.querySelector('#covChart'), {
      chart: { type: 'line', height: 300, toolbar: { show: false } },
      series: [{ name: 'COV (kg/día)', data: toNum(S.cov_kg) }],
      xaxis: xCommon, yaxis: yCommon, tooltip: tipCommon, noData,
      dataLabels: { enabled: false },
      stroke: { width: 3, curve: 'smooth' },
      grid: { strokeDashArray: 4 }
    }).render();
  }

  // === VARIACIÓN (gl) ===
  if (document.querySelector('#variacionChart')) {
    new ApexCharts(document.querySelector('#variacionChart'), {
      chart: { type: 'bar', height: 260, toolbar: { show: false } },
      series: [{ name: 'Sumatoria variación (gl)', data: toNum(S.variacion_gl) }],
      xaxis: xCommon, yaxis: yCommon, tooltip: tipCommon, noData,
      dataLabels: { enabled: false },
      grid: { strokeDashArray: 4 }
    }).render();
  }

  // === Gráfica diaria (ventas / descargue) — opcional ===
  if (document.querySelector('#graficaDiariaChart')) {
    new ApexCharts(document.querySelector('#graficaDiariaChart'), {
      chart: { type: 'bar', height: 300, stacked: true, toolbar: { show: false } },
      series: [
        { name: 'Ventas (gl)',    data: toNum(S.ventas_gl) },
        { name: 'Descargue (gl)', data: toNum(S.descargue_gl) }
      ],
      xaxis: xCommon, yaxis: yCommon, tooltip: tipCommon, noData,
      dataLabels: { enabled: false },
      legend: { position: 'top' },
      grid: { strokeDashArray: 4 }
    }).render();
  }
});
