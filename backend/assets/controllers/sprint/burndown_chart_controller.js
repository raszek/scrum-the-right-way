import { Controller } from '@hotwired/stimulus';
import {Chart} from 'chart.js';
import {format, eachDayOfInterval} from 'date-fns';

export default class extends Controller {

    static values = {
        records: Array,
        startDate: String,
    };

    connect() {
        const data = {
            labels: this.getLabels(),
            datasets: [{
                label: 'Burndown line',
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

        this.chart = new Chart(this.element, {
            type: 'line',
            data,
            options
        });
    }

    sprintEstimatedEndDateChanged(event) {
        const newSprintEndDate = event.detail.content;

        this.chart.data.labels = this.generateLabels(newSprintEndDate);

        this.chart.update();
    }

    generateLabels(newSprintEndDate) {
        const dates = eachDayOfInterval({
            start: this.startDateValue,
            end: newSprintEndDate
        })

        const labels = ['Start'];
        for (const date of dates) {
            labels.push(format(date, 'dd.MM'));
        }

        return labels;
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
