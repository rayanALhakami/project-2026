export type TransactionType = 'expense' | 'income';

export type Category = {
    id: number;
    name: string;
    type: TransactionType;
    color: string;
    icon: string | null;
    transactions_count?: number;
};

export type TransactionCategory = {
    id: number;
    name: string;
    color: string;
    icon: string | null;
};

export type Transaction = {
    id: number;
    title: string | null;
    amount: number;
    type: TransactionType;
    date: string;
    category: TransactionCategory | null;
};

export type SpendingSlice = {
    label: string;
    value: number;
    color: string;
    icon: string;
};

export type DashboardSummary = {
    spent: number;
    income: number;
    balance: number;
    spent_delta: number | null;
};

export type DashboardTrendPoint = {
    label: string;
    value: number;
};

export type MonthlyTrendPoint = {
    label: string;
    expense: number;
    income: number;
};

export type CategoryReport = SpendingSlice & {
    percent: number;
};

export type CategoryRank = {
    id: number;
    label: string;
    value: number;
    color: string;
    icon: string;
    percent: number;
};
