document.addEventListener('DOMContentLoaded', () => {
    function getMinMaxWithMargin(dataGroups, marginPercentage = 0.05) {
        let minXValue = Infinity, maxXValue = -Infinity;
        let minYValue = Infinity, maxYValue = -Infinity;

        function updateMinMax(value, key) {
            if (key === 'x') {
                if (value < minXValue) minXValue = value;
                if (value > maxXValue) maxXValue = value;
            } else if (key === 'y') {
                if (value < minYValue) minYValue = value;
                if (value > maxYValue) maxYValue = value;
            }
        }

        function extractAndUpdateMinMax(data, key) {
            if (Array.isArray(data)) {
                data.forEach(d => {
                    if (d[key] !== undefined) {
                        updateMinMax(d[key], key);
                    }
                });
            }
        }

        // Loop through the dataGroups to extract and update min-max X and Y values
        for (const parameter in dataGroups) {
            if (xColumn && !yColumn) {
                for (const xGroup in dataGroups[parameter]) {
                    for (const yGroup in dataGroups[parameter][xGroup]) {
                        const data = dataGroups[parameter][xGroup][yGroup];
                        extractAndUpdateMinMax(data, 'x');
                        extractAndUpdateMinMax(data, 'y');
                    }
                }
            } else if (!xColumn && yColumn) {
                for (const group in dataGroups[parameter]) {
                    const data = dataGroups[parameter][group];
                    extractAndUpdateMinMax(data, 'x');
                    extractAndUpdateMinMax(data, 'y');
                }
            } else if (xColumn && yColumn) {
                for (const yGroup in dataGroups[parameter]) {
                    for (const xGroup in dataGroups[parameter][yGroup]) {
                        const data = dataGroups[parameter][yGroup][xGroup];
                        extractAndUpdateMinMax(data, 'x');
                        extractAndUpdateMinMax(data, 'y');
                    }
                }
            } else {
                const data = dataGroups[parameter]['all'];
                extractAndUpdateMinMax(data, 'x');
                extractAndUpdateMinMax(data, 'y');
            }
        }

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
                            display: false,
                            text: xLabel
                        },
                        type: 'linear',
                        position: 'bottom',
                        min: minX,
                        max: maxX
                    },
                    y: {
                        title: {
                            display: false,
                            text: yLabel
                        },
                        min: minY,
                        max: maxY
                    }
                },
                plugins: {
                    legend: {
                        display: false 
                    },
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

    function createCharts(groupedData, createChartFunc, marginPercentage = 0.05) {
        const { minX, maxX, minY, maxY } = getMinMaxWithMargin(groupedData, marginPercentage);

        for (const parameter in groupedData) {
            if (hasXColumn && hasYColumn) {
                for (const yGroup in groupedData[parameter]) {
                    for (const xGroup in groupedData[parameter][yGroup]) {
                        const chartId = `chartXY_${parameter}_${yGroup}_${xGroup}`;
                        const canvasElement = document.getElementById(chartId);
                        if (canvasElement) {
                            const ctx = canvasElement.getContext('2d');
                            createChartFunc(ctx, groupedData[parameter][yGroup][xGroup], `${xGroup} vs ${yGroup}`, minX, maxX, minY, maxY);
                        }
                    }
                }
            } else if (hasXColumn) {
                for (const xGroup in groupedData[parameter]) {
                    for (const yGroup in groupedData[parameter][xGroup]) {
                        const chartId = `chartXY_${parameter}_${xGroup}`;
                        const canvasElement = document.getElementById(chartId);
                        if (canvasElement) {
                            const ctx = canvasElement.getContext('2d');
                            createChartFunc(ctx, groupedData[parameter][xGroup][yGroup], `${xGroup}`, minX, maxX, minY, maxY);
                        }
                    }
                }
            } else if (hasYColumn) {
                for (const yGroup in groupedData[parameter]) {
                    const chartId = `chartXY_${parameter}_${yGroup}`;
                    const canvasElement = document.getElementById(chartId);
                    if (canvasElement) {
                        const ctx = canvasElement.getContext('2d');
                        createChartFunc(ctx, groupedData[parameter][yGroup], `${yGroup}`, minX, maxX, minY, maxY);
                    }
                }
            } else {
                const chartId = `chartXY_${parameter}_all`;
                const canvasElement = document.getElementById(chartId);
                if (canvasElement) {
                    const ctx = canvasElement.getContext('2d');
                    createChartFunc(ctx, groupedData[parameter]['all'], 'Line Chart', minX, maxX, minY, maxY);
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
        createCharts(groupedData, createLineChart, marginPercentage);
    });

    // Initial chart creation with the default margin
    createCharts(groupedData, createLineChart);
});
