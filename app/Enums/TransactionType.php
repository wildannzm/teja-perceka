<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'pemasukan';
    case Expense = 'pengeluaran';
}
