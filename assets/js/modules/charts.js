// Charts module
import { Chart } from 'chart.js/auto';

export function initCharts() {
  console.log('Initializing charts...');

  // Line chart example
  const lineChartCtx = document.getElementById('lineChart');
  if (lineChartCtx) {
    new Chart(lineChartCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Students',
          data: [12, 19, 3, 5, 2, 3],
          borderColor: '#3498db',
          backgroundColor: 'rgba(52, 152, 219, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Student Activity Over Time'
          }
        }
      }
    });
  }

  // Bar chart example
  const barChartCtx = document.getElementById('barChart');
  if (barChartCtx) {
    new Chart(barChartCtx, {
      type: 'bar',
      data: {
        labels: ['Psychology', 'Social Work', 'Economics', 'Law', 'Management'],
        datasets: [{
          label: 'Students by Faculty',
          data: [120, 190, 80, 150, 100],
          backgroundColor: [
            '#3498db',
            '#2ecc71',
            '#e67e22',
            '#9b59b6',
            '#1abc9c'
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          title: {
            display: true,
            text: 'Students Distribution by Faculty'
          }
        }
      }
    });
  }

  // Doughnut chart example
  const doughnutChartCtx = document.getElementById('doughnutChart');
  if (doughnutChartCtx) {
    new Chart(doughnutChartCtx, {
      type: 'doughnut',
      data: {
        labels: ['Normal', 'Anxiety', 'Depression', 'Stress'],
        datasets: [{
          data: [65, 20, 10, 5],
          backgroundColor: [
            '#2ecc71',
            '#f39c12',
            '#e74c3c',
            '#95a5a6'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right',
          },
          title: {
            display: true,
            text: 'Psychological State Distribution'
          }
        }
      }
    });
  }
}