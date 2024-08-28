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
        if (hasXColumn && !hasYColumn) {
            for (const combination in dataGroups) {
                for (const xGroup in dataGroups[combination]) {
                    for (const yGroup in dataGroups[combination][xGroup]) {
                        const data = dataGroups[combination][xGroup][yGroup];
                        allXValues.push(...extractUniqueValues(data, 'x'));
                        allYValues.push(...extractUniqueValues(data, 'y'));
                    }
                }
            }
        } else if (!hasXColumn && hasYColumn) {
            for (const combination in dataGroups) {
                for (const yGroup in dataGroups[combination]) {
                    const data = dataGroups[combination][yGroup];
                    allXValues.push(...extractUniqueValues(data, 'x'));
                    allYValues.push(...extractUniqueValues(data, 'y'));
                }
            }
        } else if (hasXColumn && hasYColumn) {
            for (const combination in dataGroups) {
                for (const yGroup in dataGroups[combination]) {
                    for (const xGroup in dataGroups[combination][yGroup]) {
                        const data = dataGroups[combination][yGroup][xGroup];
                        allXValues.push(...extractUniqueValues(data, 'x'));
                        allYValues.push(...extractUniqueValues(data, 'y'));
                    }
                }
            }
        } else {
            for (const combination in dataGroups) {
                const data = dataGroups[combination]['all'];
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
    
    function calculateCorrelation(data) {
        const n = data.length;
        if (n === 0) return { r: null, r2: null };
    
        const sumX = data.reduce((sum, point) => sum + point.x, 0);
        const sumY = data.reduce((sum, point) => sum + point.y, 0);
        const sumXY = data.reduce((sum, point) => sum + point.x * point.y, 0);
        const sumX2 = data.reduce((sum, point) => sum + point.x * point.x, 0);
        const sumY2 = data.reduce((sum, point) => sum + point.y * point.y, 0);
    
        const numerator = (n * sumXY) - (sumX * sumY);
        const denominator = Math.sqrt(((n * sumX2) - (sumX * sumX)) * ((n * sumY2) - (sumY * sumY)));
    
        if (denominator === 0) return { r: 0, r2: 0 };
    
        const r = numerator / denominator;
        const r2 = r * r;
    
        return { r, r2 };
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
    
    function createScatterChart(ctx, data, label, minX, maxX, minY, maxY) {
        const deduplicatedData = deduplicateData(data); // Deduplicate data
        const aggregatedData = aggregateData(deduplicatedData); // Aggregate similar points
        
        const { r, r2 } = calculateCorrelation(aggregatedData);
        const correlationText = `r: ${r.toFixed(2)}, r²: ${r2.toFixed(2)}`;
    
        return new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: `${correlationText}`,
                    data: aggregatedData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    pointRadius: 2,
                    spanGaps: true
                }]
            },
            options: {
                animation: false,
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
                        display: true,
                        position: 'top',
                        align: 'center',
                        labels: {
                            pointStyle: 'line',
                            usePointStyle: true,
                            color: 'blue'
                        }
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
        for (const combination in groupedData) {
            if (hasXColumn && hasYColumn) {
                for (const yGroup in groupedData[combination]) {
                    for (const xGroup in groupedData[combination][yGroup]) {
                        const chartId = `chartXY_${combination}_${yGroup}_${xGroup}`;
                        const canvasElement = document.getElementById(chartId);
                        if (canvasElement) {
                            const ctx = canvasElement.getContext('2d');
                            createChartFunc(ctx, groupedData[combination][yGroup][xGroup], `${xGroup} vs ${yGroup}`, minX, maxX, minY, maxY);
                        }
                    }
                }
            } else if (hasXColumn) {
                for (const xGroup in groupedData[combination]) {
                    for (const yGroup in groupedData[combination][xGroup]) {
                        const chartId = `chartXY_${combination}_${xGroup}`;
                        const canvasElement = document.getElementById(chartId);
                        if (canvasElement) {
                            const ctx = canvasElement.getContext('2d');
                            createChartFunc(ctx, groupedData[combination][xGroup][yGroup], `${xGroup}`, minX, maxX, minY, maxY);
                        }
                    }
                }
            } else if (hasYColumn) {
                for (const yGroup in groupedData[combination]) {
                    const chartId = `chartXY_${combination}_${yGroup}`;
                    const canvasElement = document.getElementById(chartId);
                    if (canvasElement) {
                        const ctx = canvasElement.getContext('2d');
                        createChartFunc(ctx, groupedData[combination][yGroup], `${yGroup}`, minX, maxX, minY, maxY);
                    }
                }
            } else {
                const chartId = `chartXY_${combination}_all`;
                const canvasElement = document.getElementById(chartId);
                if (canvasElement) {
                    const ctx = canvasElement.getContext('2d');
                    createChartFunc(ctx, groupedData[combination]['all'], `All Data`, minX, maxX, minY, maxY);
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
        createCharts(groupedData, createScatterChart, marginPercentage);
    });
    
    createCharts(groupedData, createScatterChart);
});
