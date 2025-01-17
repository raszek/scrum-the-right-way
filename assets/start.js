import { startStimulusApp } from '@symfony/stimulus-bundle';
import Sortable from '@stimulus-components/sortable';
import Dialog from '@stimulus-components/dialog';

const app = startStimulusApp();
app.register('sortable', Sortable);
app.register('dialog', Dialog);
