document.addEventListener('DOMContentLoaded', () => {
    function getMinMaxWithMargin(dataGroups, marginPercentage = 0.05) {
        let allXValues = [];
        let allYValues = [];

        function extractValues(data, key) {
            if (Array.isArray(data)) {
                return data.flatMap(d => d[key] !== undefined ? [d[key]] : []);
            }
            return [];
        }

        if (xColumn && !yColumn) {
            for (const group in dataGroups) {
                for (const subGroup in dataGroups[group]) {
                    const data = dataGroups[group][subGroup];
                    allXValues = allXValues.concat(extractValues(data, 'x'));
                    allYValues = allYValues.concat(extractValues(data, 'y'));
                }
            }
            console.log("allXValues: ", allXValues);
            console.log("allYValues: ", allYValues); 
        } else if (!xColumn && yColumn) {
            for (const group in dataGroups) {
                const data = dataGroups[group];
                allXValues = allXValues.concat(extractValues(data, 'x'));
                allYValues = allYValues.concat(extractValues(data, 'y'));
            }
        } else if (xColumn && yColumn) {
            for (const group in dataGroups) {
                for (const subGroup in dataGroups[group]) {
                    const data = dataGroups[group][subGroup];
                    allXValues = allXValues.concat(extractValues(data, 'x'));
                    allYValues = allYValues.concat(extractValues(data, 'y'));
                }
            }
        } else {
            for (const group in dataGroups) {
                const data = dataGroups[group];
                allXValues = allXValues.concat(extractValues(data, 'x'));
                allYValues = allYValues.concat(extractValues(data, 'y'));
            }
        }

        const minXValue = allXValues.length > 0 ? Math.min(...allXValues) : 0;
        const maxXValue = allXValues.length > 0 ? Math.max(...allXValues) : 0;
        const minYValue = allYValues.length > 0 ? Math.min(...allYValues) : 0;
        const maxYValue = allYValues.length > 0 ? Math.max(...allYValues) : 0;

        const xMargin = (maxXValue - minXValue) * marginPercentage;
        const yMargin = (maxYValue - minYValue) * marginPercentage;

        return {
            minX: minXValue - xMargin,
            maxX: maxXValue + xMargin,
            minY: minYValue - yMargin,
            maxY: maxYValue + yMargin
        };
    }

    function createLineChart(ctx, data, label, minX, maxX, minY, maxY) {
        return new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 1,
                }]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: xLabel
                        },
                        type: 'linear',
                        position: 'bottom',
                        min: minX,
                        max: maxX
                    },
                    y: {
                        title: {
                            display: true,
                            text: yLabel
                        },
                        min: minY,
                        max: maxY
                    }
                },
                plugins: {
                    zoom: {
                        pan: {
                            enabled: true,
                            mode: 'xy'
                        }
                    }
                }
            }
        });
    }

    function createScatterChart(ctx, data, label, minX, maxX, minY, maxY) {
        return new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    pointRadius: 5,
                }]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: xLabel
                        },
                        type: 'linear',
                        position: 'bottom',
                        min: minX,
                        max: maxX
                    },
                    y: {
                        title: {
                            display: true,
                            text: yLabel
                        },
                        min: minY,
                        max: maxY
                    }
                },
                plugins: {
                    zoom: {
                        pan: {
                            enabled: true,
                            mode: 'xy'
                        },
                    }
                }
            }
        });
    }

    function createCharts(groupedData, isSingleParameter, createChartFunc, marginPercentage = 0.05) {
        const { minX, maxX, minY, maxY } = getMinMaxWithMargin(groupedData, marginPercentage);

        if (isSingleParameter) {
            if (hasXColumn && hasYColumn) {
                for (const yGroup in groupedData) {
                    const yGroupLabel = yGroup === 'No yGroup' ? 'Ungrouped' : yGroup;
                    for (const xGroup in groupedData[yGroup]) {
                        const xGroupLabel = xGroup === 'No xGroup' ? 'Ungrouped' : xGroup;
                        const chartId = `chartXY_${yGroupLabel}_${xGroupLabel}`;
                        const canvasElement = document.getElementById(chartId);
                        if (canvasElement) {
                            const ctx = canvasElement.getContext('2d');
                            createChartFunc(ctx, groupedData[yGroup][xGroup], `${xGroupLabel} vs ${yGroupLabel}`, minX, maxX, minY, maxY);
                        }
                    }
                }
            } else if (hasXColumn && !hasYColumn) {
                for (const xGroup in groupedData) {
                    const xGroupLabel = xGroup === 'No xGroup' ? 'Ungrouped' : xGroup;
                    const chartId = `chartXY_${xGroupLabel}`;
                    const canvasElement = document.getElementById(chartId);
                    if (canvasElement) {
                        const ctx = canvasElement.getContext('2d');
                        createChartFunc(ctx, groupedData[xGroup], `${xGroupLabel}`, minX, maxX, minY, maxY);
                    }
                }
            } else if (!hasXColumn && hasYColumn) {
                for (const yGroup in groupedData) {
                    const yGroupLabel = yGroup === 'No yGroup' ? 'Ungrouped' : yGroup;
                    const chartId = `chartXY_${yGroupLabel}`;
                    const canvasElement = document.getElementById(chartId);
                    if (canvasElement) {
                        const ctx = canvasElement.getContext('2d');
                        createChartFunc(ctx, groupedData[yGroup], `${yGroupLabel}`, minX, maxX, minY, maxY);
                    }
                }
            } else {
                const chartId = 'chartXY_all';
                const canvasElement = document.getElementById(chartId);
                if (canvasElement) {
                    const ctx = canvasElement.getContext('2d');
                    createChartFunc(ctx, groupedData['all'], 'Line Chart', minX, maxX, minY, maxY);
                }
            }
        } else {
            if (hasXColumn && hasYColumn) {
                for (const yGroup in groupedData) {
                    const yGroupLabel = yGroup === 'No yGroup' ? 'Ungrouped' : yGroup;
                    for (const xGroup in groupedData[yGroup]) {
                        const xGroupLabel = xGroup === 'No xGroup' ? 'Ungrouped' : xGroup;
                        const chartId = `chartXY_${yGroupLabel}_${xGroupLabel}`;
                        const canvasElement = document.getElementById(chartId);
                        if (canvasElement) {
                            const ctx = canvasElement.getContext('2d');
                            createChartFunc(ctx, groupedData[yGroup][xGroup], `${xGroupLabel} vs ${yGroupLabel}`, minX, maxX, minY, maxY);
                        }
                    }
                }
            } else if (hasXColumn && !hasYColumn) {
                for (const xGroup in groupedData) {
                    console.log("Xgroup: " + xGroup);
                    const xGroupLabel = xGroup === 'No xGroup' ? 'Ungrouped' : xGroup;
                    const chartId = `chartXY_${xGroupLabel}`;
                    const canvasElement = document.getElementById(chartId);
                    if (canvasElement) {
                        const ctx = canvasElement.getContext('2d');
                        createChartFunc(ctx, groupedData[xGroup], `${xGroupLabel}`, minX, maxX, minY, maxY);
                    }
                }
            } else if (!hasXColumn && hasYColumn) {
                for (const yGroup in groupedData) {
                    console.log("Ygroup: " + yGroup);
                    const yGroupLabel = yGroup === 'No yGroup' ? 'Ungrouped' : yGroup;
                    const chartId = `chartXY_${yGroupLabel}`;
                    const canvasElement = document.getElementById(chartId);
                    if (canvasElement) {
                        const ctx = canvasElement.getContext('2d');
                        createChartFunc(ctx, groupedData[yGroup], `${yGroupLabel}`, minX, maxX, minY, maxY);
                    }
                }
            } else {
                const chartId = 'chartXY_all';
                const canvasElement = document.getElementById(chartId);
                if (canvasElement) {
                    const ctx = canvasElement.getContext('2d');
                    createChartFunc(ctx, groupedData['all'], 'Scatter Chart', minX, maxX, minY, maxY);
                }
            }
        }
    }

    const marginRange = document.getElementById('marginRange');
    const rangeValue = document.getElementById('rangeValue');

    marginRange.addEventListener('input', function () {
        const marginPercentage = marginRange.value / 100;
        rangeValue.textContent = `${marginRange.value}%`;

        // Clear existing charts before creating new ones
        Chart.helpers.each(Chart.instances, function (instance) {
            instance.destroy();
        });

        // Recreate charts with the new margin percentage
        createCharts(groupedData, isSingleParameter, isSingleParameter ? createLineChart : createScatterChart, marginPercentage);
    });

    // Initial chart creation with the default margin
    createCharts(groupedData, isSingleParameter, isSingleParameter ? createLineChart : createScatterChart);

});
