import { Controller } from '@hotwired/stimulus';
import {Chart, registerables} from 'chart.js';

export default class extends Controller {

    static values = {
        records: Array
    };

    connect() {
        const data = {
            labels: this.getLabels(),
            datasets: [{
                label: 'Burndown Chart',
                data: this.getValues(),
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        };

        const options = {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };

        Chart.register(...registerables);

        new Chart(this.element, {
            type: 'line',
            data,
            options
        });
    }

    getLabels() {
        return this.recordsValue
            .map((record) => record.date);
    }

    getValues() {
        return this.recordsValue
            .filter((record) => record.storyPoints !== null)
            .map((record) => record.storyPoints);
    }

}
