class Calculate {
    constructor(num1, num2) {
        this.num1 = num1;
        this.num2 = num2;
    }

    Multiple() {
        let result = parseFloat(this.num1) * parseFloat(this.num2);
        return result;
    }
}