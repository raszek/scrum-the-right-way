
export class ImmutableDateTime {

    constructor(date) {
        this.date = date;
    }

    static now() {
        return new ImmutableDateTime(new Date());
    }

    static create(
        year = null,
        month = null,
        day = null,
        hour = null,
        minute = null,
        second = null
    ) {
        const date = new Date(
            year,
            month ? month - 1 : null,
            day,
            hour,
            minute,
            second
        );

        return new ImmutableDateTime(date);
    }

    /**
     * @return {number}
     */
    timestamp() {
        return Math.floor(this.date.getTime() / 1000);
    }

    /**
     * @param {number} hours
     * @return {ImmutableDateTime}
     */
    subHours(hours) {
        return ImmutableDateTime.create(
            this.getFullYear(),
            this.getMonth(),
            this.getDate(),
            this.getHours() - hours,
            this.getMinutes(),
            this.getSeconds()
        );
    }

    getMonth() {
        return this.date.getMonth() + 1;
    }

    getDate() {
        return this.date.getDate();
    }

    getFullYear() {
        return this.date.getFullYear();
    }

    getHours() {
        return this.date.getHours();
    }

    getMinutes() {
        return this.date.getMinutes();
    }

    getSeconds() {
        return this.date.getSeconds();
    }
}
