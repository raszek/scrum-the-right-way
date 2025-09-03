export class DateTime {

    constructor(date) {
        this.date = new Date(date);
    }

    defaultFormat() {
        const date = String(this.getDate()).padStart(2, '0');
        const month = String(this.getMonth()).padStart(2, '0');
        const year = String(this.getYear()).padStart(4, '0');

        return `${date}.${month}.${year}`;
    }

    getDate() {
        return this.date.getDate();
    }

    getMonth() {
        return this.date.getMonth() + 1;
    }

    getYear() {
        return this.date.getFullYear();
    }
}
