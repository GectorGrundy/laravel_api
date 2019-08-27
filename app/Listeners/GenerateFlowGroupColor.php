<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\FlowGroupCreated;

class GenerateFlowGroupColor
{
    public const COLORS = [
        '#f44336', '#9c27b0', '#673ab7', '#2196f3', '#00bcd4', '#009688', '#4caf50', '#cddc39', '#ead302', '#ffc107',
        '#ff5722', '#607d8b',
    ];

    public function handle(FlowGroupCreated $event)
    {
        $event->flow_group->color = array_random(self::COLORS, 1)[0];
        $event->flow_group->save();
    }
}
