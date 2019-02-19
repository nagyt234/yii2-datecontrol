<?php

/**
 * @package   yii2-datecontrol
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2018
 * @version   1.9.7
 */

namespace kartik\datecontrol\controllers;

use DateTimeZone;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\web\Controller;
use yii\web\Response;
use kartik\datecontrol\DateControl;

/**
 * ParseController class manages the actions for date conversion via ajax from display to save.
 */
class ParseController extends Controller
{
    /**
     * Convert display date for saving to model.
     *
     * @return array JSON encoded HTML output
     */
    public function actionConvert()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;
        $post = $req->post();
        if (!isset($post['displayDate'])) {
            return ['status' => 'error', 'output' => 'No display date found'];
        }
        $saveFormat = $req->post('saveFormat', '');
        $saveTimezone = $req->post('saveTimezone', '');
        $dispFormat = $req->post('dispFormat', '');
        $dispTimezone = $req->post('dispTimezone', '');
        $displayDate = $req->post('displayDate', '');
        $settings = $req->post('settings', []);

        // https://github.com/alexsuter/yii2-datecontrol - 14669c89b0827f84b840f7fe2bde08a451557eff
        // Russian dates ends with \r. - see https://github.com/yiisoft/yii2/issues/17144
        if (StringHelper::endsWith($dispFormat, '.')) {
            $dispFormat = substr($dispFormat, 0, strlen($dispFormat) - 4);
            $displayDate = substr($post['displayDate'], 0, strlen($post['displayDate']) - 4);
        }
        
        // https://github.com/alexsuter/yii2-datecontrol - 17776e5230be0245be2876a60fd2c7b85828364d
        // Only apply Timezone settings on DateTime Formats
        if (ArrayHelper::getValue($post, 'type') != DateControl::FORMAT_DATETIME) {
            $dispTimezone = null;
            $saveTimezone = null;
        }

        $date = DateControl::getTimestamp($displayDate, $dispFormat, $dispTimezone, $settings);
        if (empty($date) || !$date) {
            $value = '';
        } elseif ($saveTimezone != null) {
            $value = $date->setTimezone(new DateTimeZone($saveTimezone))->format($saveFormat);
        } else {
            $value = $date->format($saveFormat);
        }
        return ['status' => 'success', 'output' => $value];
    }
}
