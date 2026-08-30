import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
window.flatpickr = flatpickr;
window.flatpickrIndonesian = Indonesian;

import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect/index.js';
window.flatpickrMonthSelect = monthSelectPlugin;

import Chart from 'chart.js/auto';
window.Chart = Chart;

import Swal from 'sweetalert2';
window.Swal = Swal;
