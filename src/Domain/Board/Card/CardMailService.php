<?php

namespace App\Domain\Board\Card;

use Psr\Log\LoggerInterface;
use App\Domain\Board\Stack\StackService;
use App\Domain\User\UserService;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Main\Helper;
use Slim\Routing\RouteParser;

class CardMailService {

    private $logger;
    private $mapper;
    private $helper;
    private $stack_service;
    private $user_service;
    private $translation;
    private $settings;
    private $router;

    public function __construct(LoggerInterface $logger, CardMapper $mapper, StackService $stack_service, UserService $user_service, Translator $translation, Settings $settings, Helper $helper, RouteParser $router) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->helper = $helper;
        $this->stack_service = $stack_service;
        $this->user_service = $user_service;
        $this->translation = $translation;
        $this->settings = $settings;
        $this->router = $router;
    }

    public function sendReminder() {

        $due_cards = $this->mapper->getCardReminder();
        $users = $this->user_service->getAll();
        $stacks = $this->stack_service->getAll();

        $subject = $this->translation->getTranslatedString('MAIL_CARD_REMINDER');

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language, NULL, NULL);
        $fmt->setPattern($dateFormatPHP['month_name_full']);


        foreach ($due_cards as $user_id => $cards) {
            $user = $users[$user_id];

            if ($user->mail && $user->mails_board_reminder == 1) {
                $variables = array(
                    'header' => '',
                    'subject' => $subject,
                    'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                    'content' => $this->translation->getTranslatedString('MAIL_CARD_REMINDER_DETAIL')
                );

                $user_cards = $due_cards[$user_id];

                $mail_content = '';

                foreach ($user_cards as $today => $stacks) {

                    if ($today == 1) {
                        $mail_content .= '<h2>' . $this->translation->getTranslatedString('TODAY') . ':</h2>';
                    } else {
                        $mail_content .= '<h2>' . $this->translation->getTranslatedString('OVERDUE') . ':</h2>';
                    }
                    foreach ($stacks as $board_name => $board) {
                        $url = $this->helper->getBaseURL() . $this->router->urlFor('boards_view', array('hash' => $board["hash"]));
                        $mail_content .= '<h3><a href="' . $url . '">Board: ' . $board_name . '</a></h3>';
                        $mail_content .= '<ul>';

                        foreach ($board["stacks"] as $stack_name => $cards) {
                            $mail_content .= '<li>' . $stack_name;
                            $mail_content .= '  <ul>';
                            $mail_content .= implode('', array_map(function($c) use ($fmt, $today) {
                                        $output = '<li>' . $c["title"];
                                        if ($today != 1) {
                                            $dateObj = new \DateTime($c["date"]);
                                            $output .= ' (' . $fmt->format($dateObj) . ')';
                                        }
                                        $output .= '</li>';
                                        return $output;
                                    }, $cards));
                            $mail_content .= '  </ul>';
                            $mail_content .= '</li>';
                        }
                        $mail_content .= '</ul>';
                    }
                }

                $variables['extra'] = $mail_content;

                if (!empty($mail_content)) {
                    $this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
                }
            }
        }
        return true;
    }

}
