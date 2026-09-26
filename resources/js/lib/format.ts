export function formatNumber(value: number): string {
    return value.toLocaleString('ar-SA');
}

export function formatCurrency(value: number): string {
    return `${value.toLocaleString('ar-SA')} ر.س`;
}

const MONTHS = [
    'يناير',
    'فبراير',
    'مارس',
    'أبريل',
    'مايو',
    'يونيو',
    'يوليو',
    'أغسطس',
    'سبتمبر',
    'أكتوبر',
    'نوفمبر',
    'ديسمبر',
];

export function formatDate(date: string): string {
    const parsed = new Date(`${date}T00:00:00`);
    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    const day = parsed.getDate().toLocaleString('ar-SA');
    const month = MONTHS[parsed.getMonth()];

    return `${day} ${month} ${parsed.getFullYear().toLocaleString('ar-SA')}`;
}
