document.addEventListener('DOMContentLoaded', () => {
    function getMinMaxWithMargin(dataGroups, marginPercentage = 0.05) {
        let allXValues = [];
        let allYValues = [];

        function extractUniqueValues(data, key) {
            const uniqueValues = new Set();
            if (Array.isArray(data)) {
                data.forEach(d => {
                    const value = d[key];
                    if (value !== undefined) {
                        uniqueValues.add(value);
                    }
                });
            }
            return Array.from(uniqueValues);
        }

        // Loop through the dataGroups to extract all X and Y values
        if (xColumn && !yColumn) {
            for (const parameter in dataGroups) {
                for (const xGroup in dataGroups[parameter]) {
                    for (const yGroup in dataGroups[parameter][xGroup]) {
                        const data = dataGroups[parameter][xGroup][yGroup];
                        allXValues.push(...extractUniqueValues(data, 'x'));
                        allYValues.push(...extractUniqueValues(data, 'y'));
                    }
                }
            }
        } else if (!xColumn && yColumn) {
            for (const parameter in dataGroups) {
                for (const group in dataGroups[parameter]) {
                    const data = dataGroups[parameter][group];
                    allXValues.push(...extractUniqueValues(data, 'x'));
                    allYValues.push(...extractUniqueValues(data, 'y'));
                }
            }
        } else if (xColumn && yColumn) {
            for (const parameter in dataGroups) {
                for (const yGroup in dataGroups[parameter]) {
                    for (const xGroup in dataGroups[parameter][yGroup]) {
                        const data = dataGroups[parameter][yGroup][xGroup];
                        allXValues.push(...extractUniqueValues(data, 'x'));
                        allYValues.push(...extractUniqueValues(data, 'y'));
                    }
                }
            }
        } else {
            for (const parameter in dataGroups) {
                const data = dataGroups[parameter]['all'];
                allXValues.push(...extractUniqueValues(data, 'x'));
                allYValues.push(...extractUniqueValues(data, 'y'));
            }
        }

        // Compute min and max values iteratively
        function getMinMax(arr) {
            if (arr.length === 0) return { min: 0, max: 0 };
            
            let min = arr[0];
            let max = arr[0];
            
            for (let i = 1; i < arr.length; i++) {
                if (arr[i] < min) min = arr[i];
                if (arr[i] > max) max = arr[i];
            }
            
            return { min, max };
        }

        const { min: minXValue, max: maxXValue } = getMinMax(allXValues);
        const { min: minYValue, max: maxYValue } = getMinMax(allYValues);

        const xMargin = (maxXValue - minXValue) * marginPercentage;
        const yMargin = (maxYValue - minYValue) * marginPercentage;

        return {
            minX: minXValue - xMargin,
            maxX: maxXValue + xMargin,
            minY: minYValue - yMargin,
            maxY: maxYValue + yMargin
        };
    }

    function deduplicateData(data) {
        const uniquePoints = {};
        return data.filter(point => {
            const key = `${point.x},${point.y}`;
            if (uniquePoints[key]) {
                return false;
            } else {
                uniquePoints[key] = true;
                return true;
            }
        });
    }

    function aggregateData(data) {
        const aggregatedData = {};
        data.forEach(point => {
            const key = `${point.x},${point.y}`;
            if (!aggregatedData[key]) {
                aggregatedData[key] = { ...point, count: 1 };
            } else {
                aggregatedData[key].count += 1;
                aggregatedData[key].x = (aggregatedData[key].x * (aggregatedData[key].count - 1) + point.x) / aggregatedData[key].count;
                aggregatedData[key].y = (aggregatedData[key].y * (aggregatedData[key].count - 1) + point.y) / aggregatedData[key].count;
            }
        });
        return Object.values(aggregatedData);
    }

    function createLineChart(ctx, data, label, minX, maxX, minY, maxY) {
        const deduplicatedData = deduplicateData(data); // Deduplicate data
        const aggregatedData = aggregateData(deduplicatedData); // Aggregate similar points

        return new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: label,
                    data: aggregatedData,
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
