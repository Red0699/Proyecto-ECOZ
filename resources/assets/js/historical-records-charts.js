'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let cardColor, headingColor, labelColor, borderColor;
  if (typeof isDarkStyle !== 'undefined' && isDarkStyle) {
    cardColor = config.colors_dark.cardColor;
    headingColor = config.colors_dark.headingColor;
    labelColor = config.colors_dark.textMuted;
    borderColor = config.colors_dark.borderColor;
  } else {
    cardColor = config.colors.cardColor;
    headingColor = config.colors.headingColor;
    labelColor = config.colors.textMuted;
    borderColor = config.colors.borderColor;
  }

  // --- 1. CORRECCIÓN: Gráfica Temperatura vs Humedad (Ejes Múltiples) ---
  const tempHumidityChartEl = document.querySelector('#tempHumidityChart');
  if (tempHumidityChartEl && typeof chartData !== 'undefined') {
    const tempHumidityChart = new ApexCharts(tempHumidityChartEl, {
      chart: { type: 'line', height: 350, toolbar: { show: true } },
      series: [
        { name: 'Temperatura (°C)', type: 'line', data: chartData.temperatures },
        { name: 'Humedad (%)', type: 'line', data: chartData.humidities }
      ],
      xaxis: { categories: chartData.time_labels, labels: { style: { colors: labelColor } } },
      // **NUEVO**: Definimos dos ejes Y
      yaxis: [
        { // Eje para la Temperatura
          seriesName: 'Temperatura (°C)',
          axisTicks: { show: true },
          axisBorder: { show: true, color: config.colors.primary },
          labels: { style: { colors: config.colors.primary }, formatter: (value) => `${value.toFixed(1)} °C` },
          title: { text: "Temperatura (°C)", style: { color: config.colors.primary } },
        },
        { // Eje para la Humedad
          seriesName: 'Humedad (%)',
          opposite: true, // Pone este eje a la derecha
          axisTicks: { show: true },
          axisBorder: { show: true, color: config.colors.info },
          labels: { style: { colors: config.colors.info }, formatter: (value) => `${value.toFixed(1)} %` },
          title: { text: "Humedad (%)", style: { color: config.colors.info } },
        }
      ],
      colors: [config.colors.primary, config.colors.info],
      stroke: { curve: 'smooth', width: [2, 2] },
      grid: { borderColor: borderColor },
      legend: { show: true }
    });
    tempHumidityChart.render();
  }

  // --- 2. Gráfica: Emisiones vs Ventas (Sin cambios) ---
  const emissionsSalesChartEl = document.querySelector('#emissionsSalesChart');
  if (emissionsSalesChartEl && typeof kpis !== 'undefined') {
    // ... (este código se mantiene igual)
  }

  // --- 3. CORRECCIÓN: Gráfica Presiones de Vapor (Eje Y ajustado) ---
  const pressuresChartEl = document.querySelector('#pressuresChart');
  if (pressuresChartEl && typeof chartData !== 'undefined') {
    const pressuresChart = new ApexCharts(pressuresChartEl, {
      chart: { type: 'line', height: 350, toolbar: { show: true } },
      series: [
        { name: 'Presión Octano (MmHg)', data: chartData.octane_pressures },
        { name: 'Presión Heptano (MmHg)', data: chartData.heptane_pressures },
        { name: 'Presión Tolueno (MmHg)', data: chartData.toluene_pressures }
      ],
      xaxis: { categories: chartData.time_labels, labels: { style: { colors: labelColor } } },
      // **NUEVO**: Ajustamos el rango del eje Y para hacer "zoom"
      yaxis: {
        min: chartData.min_pressure, // Mínimo calculado en el controlador
        max: chartData.max_pressure, // Máximo calculado en el controlador
        tickAmount: 5, // Número de divisiones en el eje
        labels: { style: { colors: labelColor } }
      },
      colors: [config.colors.danger, config.colors.secondary, config.colors.warning],
      stroke: { curve: 'smooth', width: 2 },
      grid: { borderColor: borderColor },
      legend: { show: true }
    });
    pressuresChart.render();
  }
});