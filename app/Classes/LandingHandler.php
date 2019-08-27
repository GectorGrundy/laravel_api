<?php
declare(strict_types=1);

namespace App\Classes;

use App\Support\LandingCallbackOperator;
use Cache;
use App\Models\{
    Transit, Visitor, Flow, Landing
};
use App\Http\GoDataContainer;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Класс для работы с лендингами
 */
class LandingHandler
{
    use DispatchesJobs;

    private $landing;
    private $visitor;
    /**
     * @var GoDataContainer
     */
    private $data_container;

    public function __construct(Landing $landing, Visitor $visitor, GoDataContainer $data_container)
    {
        $this->landing = $landing;
        $this->visitor = $visitor;
        $this->data_container = $data_container;
    }

    /**
     * Получаем лендинг для перенаправление на нее посетителя
     *
     * @return Landing
     */
    public function getLandingForShow(): Landing
    {
        $flow = $this->data_container->getFlow();
        $visitor = $this->data_container->getVisitor();

        // Если это поток fallback паблишера - выбираем случайных лендинг цели
        if ($flow['publisher_id'] === (int)config('env.fallback_publisher_id')) {

            $target_id = $this->data_container->getTransit()['target_id'];
            $landings = Landing::where('target_id', $target_id)->get();

            if (!$landings->count()) {
                throw new \LogicException("Для цели {$target_id} нету лендингов.");
            }

            return $landings->random();
        }

        $flow_landings = $flow->landings;

        // Если в сплите потока указано запоминать историю посещений
        $visitor_landing_id = 0;
        if ((int)$flow['is_remember_landing'] === 1) {
            // Получаем id лендинга, если он переходил раньше на него
            $visitor_landing_id = $this->visitor->getViewedLandingIdForFlow(
                $visitor['info'],
                $flow['offer_id'],
                $flow['hash']
            );
        }

        // Получаем идентификаторы лендингов потока
        $flow_landing_ids = $flow_landings->pluck('id')->toArray();

        // Если в истории у посетителя есть показанный лендинг и он есть в текущих настройках потока - берем его
        if ($visitor_landing_id > 0 && in_array($visitor_landing_id, $flow_landing_ids)) {

            $landing = $this->landing->getById($visitor_landing_id);

        } else {
            // Получаем лендинг для перенаправления из списка доступных у потока
            $landing = $this->getLandingForShowByRandom($flow, $flow_landings);

        }

        return $landing;
    }

    /**
     * Получаем лендинг из списка доступных у потока
     *
     * @param Flow $flow
     * @param Collection $flow_landings
     * @return Landing
     */
    private function getLandingForShowByRandom(Flow $flow, Collection $flow_landings): Landing
    {
        // Получаем статистику переходов на лендинги потока
        $showed_landings = $this->getShowedLandingsInFlow($flow['id']);

        $exists_not_showed = FALSE;

        foreach ($flow_landings AS $flow_landing) {

            //Если в доступных для перехода лендингах есть те, на которые еще не переходили - отдаем первый попавшийся
            if (!array_key_exists($flow_landing['id'], $showed_landings)) {
                $exists_not_showed = TRUE;

                $landing_id = $flow_landing['id'];

                $showed_landings[$landing_id]['count_show'] = 1;
                $showed_landings[$landing_id]['landing_id'] = $landing_id;
                break;
            }
        }

        // Если нету лендингов, на которые еще не переходили посетители
        if (!$exists_not_showed) {

            $showed_landings_by_random = collect($showed_landings);

            //Сортировка по кол-ву показов(ASC)
            $showed_landings_by_random = $showed_landings_by_random->sortBy('count_show')->toArray();

            //Выбираем лендинг, у которой было наименьшее кол-во показов
            $landing_id = current($showed_landings_by_random)['landing_id'];

            //Инкремент кол-во показов
            $showed_landings[$landing_id]['count_show'] += 1;
        }

        //Записуем обновленную статистику по показанным лендингам потока
        $this->setShowedLandingsInFlow($flow['id'], $showed_landings);

        return $this->landing->getById($landing_id);
    }

    /**
     * Получение статистики переходов на лендинги потока
     *
     * @param $flow_id
     * @return array
     */
    private function getShowedLandingsInFlow($flow_id)
    {
        $key = "go:getShowedLandingsInFlow:{$flow_id}";

        $showed_landings = Cache::tags("flow-{$flow_id}")->get($key);

        if (is_null($showed_landings)) {
            $showed_landings = [];
        } else {
            $showed_landings = json_decode($showed_landings, TRUE);
        }

        return $showed_landings;
    }

    /**
     * Записуем в кеш статистику переходов на прелендинг потока
     *
     * @param $flow_id
     * @param $showed_landings
     */
    public function setShowedLandingsInFlow($flow_id, $showed_landings)
    {
        $key = "go:getShowedLandingsInFlow:{$flow_id}";

        Cache::tags("flow-{$flow_id}")->forever($key, json_encode($showed_landings));
    }

    public function getBaseTag(): string
    {
        $site = $this->data_container->getSite();
        $site_type = $site instanceof Transit ? 'prelanding' : 'landing';
        $path = $this->getBaseTagPath($site_type, $site['hash']);

        return sprintf('<base href="%s/">', $path);
    }

    public function getLandingModals()
    {
        $operator_class = LandingCallbackOperator::getClassByLocaleCode($this->data_container->getLocale()['code']);

        return view('parts.landing_modals', [
            'country_code' => $this->data_container->getVisitor()['geo_ids']['country_code'],
            'operator_class' => $operator_class,
        ]);
    }

    public function getBaseTagPath(string $site_type, string $site_hash): string
    {
        // "apollofiles" it's just random string
        return sprintf('/apollofiles/%s/%s', $site_type, $site_hash);
    }

    private function getActivityTrackerScriptFile(): string
    {
        return \File::get(public_path('js/activity_tracker.js'));
    }

    /**
     * Получение флага, для определения нужло ли отображать ссылку на перезаполение формы заказа на success page
     *
     * @param $is_iframe
     * @return bool
     */
    public function getShowBackLinkOnSuccess($is_iframe)
    {
        return !$is_iframe;
    }
}