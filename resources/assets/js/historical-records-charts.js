// resources/assets/js/historical-records-charts.js
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  if (typeof ApexCharts === 'undefined') return;

  const S = window.series || { labels: [] };
  const F = window.filterMeta || { mode: 'custom' };

  // ---- Helpers -------------------------------------------------------------
  const PALETTE = [
    '#1A73E8','#E91E63','#00C853','#FF9800','#8E24AA',
    '#00BCD4','#9C27B0','#43A047','#F4511E','#3F51B5',
    '#FDD835','#26A69A','#5C6BC0','#EC407A','#7CB342'
  ];
  const colorsFor = (n, start = 0) => Array.from({ length: n }, (_, i) => PALETTE[(i + start) % PALETTE.length]);

  // labels: array seguro
  const rawLabels = Array.isArray(S.labels)
    ? S.labels
    : S.labels && typeof S.labels === 'object'
      ? Object.values(S.labels)
      : [];

  // Mostrar meses bonitos cuando es anual
  const MONTHS = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
  const prettyMonth = (ym) => {
    // ym = 'YYYY-MM'
    const m = String(ym).split('-')[1];
    const idx = m ? (parseInt(m,10)-1) : null;
    return idx!=null && idx>=0 && idx<12 ? MONTHS[idx] : ym;
  };
  const labels = (F.mode === 'year')
    ? rawLabels.map(prettyMonth)
    : rawLabels;

  const toNum = (arr) => {
    if (!arr) return [];
    const vals = Array.isArray(arr) ? arr : (typeof arr === 'object' ? Object.values(arr) : []);
    return vals.map(v => (v === null || v === '' || isNaN(Number(v)) ? null : Number(v)));
  };

  const yFmt = v => (v == null || isNaN(v) ? '' : Number(v).toFixed(2));
  const noData = { text: 'Sin datos', align: 'center' };
  const baseAxes = (cats) => ({
    xaxis: { type: 'category', categories: cats, labels: { rotate: 0 } },
    yaxis: { labels: { formatter: yFmt } },
    tooltip: {
      theme: 'light',
      x: { formatter: (_, opts) => cats?.[opts.dataPointIndex] ?? '' },
      y: { formatter: yFmt }
    },
    grid: { strokeDashArray: 4 }
  });

  // ---- SERIES (todas numéricas) -------------------------------------------
  const inv   = toNum(S.inventario);
  const psi   = toNum(S.psi_max);
  const cov   = toNum(S.cov_kg);
  const vEvap = toNum(S.var_evap_gl);
  const vTot  = toNum(S.var_total_gl);

  // ---- Inventario ----------------------------------------------------------
  // Anual -> línea; otros -> pastel
  if (document.querySelector('#invChart')) {
    if (F.mode === 'year' || inv.length <= 1) {
      new ApexCharts(document.querySelector('#invChart'), {
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        series: [{ name: 'Inventario (gl, prom.)', data: inv }],
        colors: [PALETTE[0]],
        stroke: { width: 3, curve: 'smooth' },
        markers: { size: 3, colors: [PALETTE[0]], strokeColors: '#fff', strokeWidth: 2 },
        dataLabels: { enabled: false },
        ...baseAxes(labels),
        noData
      }).render();
    } else {
      new ApexCharts(document.querySelector('#invChart'), {
        chart: { type: 'pie', height: 340, toolbar: { show: false } },
        series: inv,
        labels,
        colors: colorsFor(inv.length),
        legend: {
          position: 'right',
          formatter: (val, opts) => `${val}: ${(inv[opts.seriesIndex] ?? 0).toFixed(2)} gl`
        },
        dataLabels: { enabled: true, formatter: val => `${val.toFixed(1)}%` },
        noData
      }).render();
    }
  }

  // ---- Presión -------------------------------------------------------------
  if (document.querySelector('#presionChart')) {
    new ApexCharts(document.querySelector('#presionChart'), {
      chart: { type: 'line', height: 300, toolbar: { show: false } },
      series: [{ name: (F.mode==='year'?'Psi máx. mensual':'Psi máx.'), data: psi }],
      colors: [PALETTE[3]],
      stroke: { width: 3, curve: 'smooth' },
      markers: { size: 3, colors: [PALETTE[3]], strokeColors: '#fff', strokeWidth: 2 },
      dataLabels: { enabled: false },
      ...baseAxes(labels),
      noData
    }).render();
  }

  // ---- COV -----------------------------------------------------------------
  if (document.querySelector('#covChart')) {
    new ApexCharts(document.querySelector('#covChart'), {
      chart: { type: 'bar', height: 300, toolbar: { show: false } },
      series: [{ name: (F.mode==='year'?'COV mensual (kg)':'COV (kg)'), data: cov }],
      colors: colorsFor(cov.length, 5),
      plotOptions: { bar: { distributed: true, borderRadius: 6 } },
      dataLabels: { enabled: false },
      ...baseAxes(labels),
      noData
    }).render();
  }

  // ---- Variación / Pérdida de galonaje ------------------------------------
  if (document.querySelector('#variacionChart')) {
    new ApexCharts(document.querySelector('#variacionChart'), {
      chart: { type: 'bar', height: 300, toolbar: { show: false } },
      series: [
        { name: (F.mode==='year'?'Por evaporación (mensual)':'Por evaporación'), data: vEvap },
        { name: (F.mode==='year'?'Total EDS (mensual)':'Total EDS'), data: vTot }
      ],
      colors: [PALETTE[1], PALETTE[2]],
      plotOptions: { bar: { columnWidth: '45%', endingShape: 'rounded' } },
      dataLabels: { enabled: false },
      legend: { position: 'top' },
      ...baseAxes(labels),
      noData
    }).render();
  }
});
