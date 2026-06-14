import './bootstrap';
import { Device } from '@twilio/voice-sdk';
import {
    Chart,
    LineController, BarController, DoughnutController,
    LineElement, BarElement, ArcElement, PointElement,
    CategoryScale, LinearScale,
    Tooltip, Legend, Filler,
} from 'chart.js';

Chart.register(
    LineController, BarController, DoughnutController,
    LineElement, BarElement, ArcElement, PointElement,
    CategoryScale, LinearScale,
    Tooltip, Legend, Filler
);

window.Device = Device;
window.Chart  = Chart;
