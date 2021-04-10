let exports = {};

const intlUsd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

exports.formatTokenAmount = (num) => {
    const number = Math.abs(num);

    let decimals = 8;

    if (number > 100000) {
        decimals = 1;
    } else if (number > 10000) {
        decimals = 2;
    } else if (number > 1000) {
        decimals = 2;
    } else if (number > 100) {
        decimals = 3;
    } else if (number > 10) {
        decimals = 4;
    } else if (number > 1) {
        decimals = 5;
    } else if (number > 0.1) {
        decimals = 6;
    } else if (number > 0.01) {
        decimals = 7;
    } else if (number > 0.01) {
        decimals = 7;
    }

    return new Intl.NumberFormat('en-US', { minimumFractionDigits: decimals }).format(number);
};

exports.formatCurrency = (num) => {
    return intlUsd.format(num);
}

export default exports;