import {Chart, registerables} from 'chart.js';

import './start.js';

import '@toast-ui/editor/dist/toastui-editor.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import 'iconoir/css/iconoir-regular.min.css';
import 'glightbox/dist/css/glightbox.min.css';

Chart.register(...registerables);
