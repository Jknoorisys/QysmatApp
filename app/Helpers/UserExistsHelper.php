<?php

use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\Transactions;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Stripe\Stripe;
use Willywes\AgoraSDK\RtcTokenBuilder;

    function userExist($login_id, $user_type)
    {
        if ($user_type == 'singleton') {
            $user = Singleton::find($login_id);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->is_verified != 'verified') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-verified'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            $linked = ParentChild::where('singleton_id','=',$login_id)->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.singleton-not-linked'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

        } elseif ($user_type == 'parent') {
            $user = ParentsModel::find($login_id);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->is_verified != 'verified') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-verified'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            $linked = ParentChild::where('parent_id','=',$login_id)->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.parent-not-linked'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        }
    }

    function userFound($login_id, $user_type)
    {
        if ($user_type == 'singleton') {
            $user = Singleton::find($login_id);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        } elseif ($user_type == 'parent') {
            $user = ParentsModel::find($login_id);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        }
    }

    function parentExist($login_id, $user_type, $singleton_id)
    {
        if ($user_type == 'singleton') {
            $user = Singleton::find($login_id);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->is_verified != 'verified') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-verified'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            $linked = ParentChild::where('singleton_id','=',$login_id)->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.singleton-not-linked'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        } elseif ($user_type == 'parent') {
            $user = ParentsModel::find($login_id);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->is_verified != 'verified') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-verified'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            $linked = ParentChild::where([['parent_id','=',$login_id], ['singleton_id','=',$singleton_id]])->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.parent-not-linked'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        }
    }

    function detect_disposable_email($email) {

        $not_allowed = array(
            '0815.ru',
            '0wnd.net',
            '0wnd.org',
            '10minutemail.co.za',
            '10minutemail.com',
            '123-m.com',
            '1fsdfdsfsdf.tk',
            '1pad.de',
            '20minutemail.com',
            '21cn.com',
            '2fdgdfgdfgdf.tk',
            '2prong.com',
            '30minutemail.com',
            '33mail.com',
            '3trtretgfrfe.tk',
            '4gfdsgfdgfd.tk',
            '4warding.com',
            '5ghgfhfghfgh.tk',
            '6hjgjhgkilkj.tk',
            '6paq.com',
            '7tags.com',
            '9ox.net',
            'a-bc.net',
            'agedmail.com',
            'ama-trade.de',
            'amilegit.com',
            'amiri.net',
            'amiriindustries.com',
            'anonmails.de',
            'anonymbox.com',
            'antichef.com',
            'antichef.net',
            'antireg.ru',
            'antispam.de',
            'antispammail.de',
            'armyspy.com',
            'artman-conception.com',
            'azmeil.tk',
            'baxomale.ht.cx',
            'boxomail.live',
            'beefmilk.com',
            'bigstring.com',
            'binkmail.com',
            'bio-muesli.net',
            'bobmail.info',
            'bodhi.lawlita.com',
            'bofthew.com',
            'bootybay.de',
            'boun.cr',
            'bouncr.com',
            'breakthru.com',
            'brefmail.com',
            'bsnow.net',
            'bspamfree.org',
            'bugmenot.com',
            'bund.us',
            'burstmail.info',
            'buymoreplays.com',
            'byom.de',
            'c2.hu',
            'card.zp.ua',
            'casualdx.com',
            'cdfaq.com',
            'cek.pm',
            'centermail.com',
            'centermail.net',
            'chammy.info',
            'childsavetrust.org',
            'chogmail.com',
            'choicemail1.com',
            'civikli.com',
            'clixser.com',
            'cmail.net',
            'cmail.org',
            'coldemail.info',
            'cool.fr.nf',
            'courriel.fr.nf',
            'courrieltemporaire.com',
            'crapmail.org',
            'cust.in',
            'cuvox.de',
            'd3p.dk',
            'dacoolest.com',
            'dandikmail.com',
            'dayrep.com',
            'dcemail.com',
            'deadaddress.com',
            'deadspam.com',
            'delikkt.de',
            'despam.it',
            'despammed.com',
            'devnullmail.com',
            'dfgh.net',
            'digitalsanctuary.com',
            'dingbone.com',
            'disposableaddress.com',
            'disposableemailaddresses.com',
            'disposableinbox.com',
            'dispose.it',
            'dispostable.com',
            'dodgeit.com',
            'dodgit.com',
            'donemail.ru',
            'dontreg.com',
            'dontsendmespam.de',
            'drdrb.net',
            'dump-email.info',
            'dumpandjunk.com',
            'dumpyemail.com',
            'e-mail.com',
            'e-mail.org',
            'e4ward.com',
            'easytrashmail.com',
            'einmalmail.de',
            'einrot.com',
            'eintagsmail.de',
            'emailgo.de',
            'emailias.com',
            'emaillime.com',
            'emailsensei.com',
            'emailtemporanea.com',
            'emailtemporanea.net',
            'emailtemporar.ro',
            'emailtemporario.com.br',
            'emailthe.net',
            'emailtmp.com',
            'emailwarden.com',
            'emailx.at.hm',
            'emailxfer.com',
            'emeil.in',
            'emeil.ir',
            'emz.net',
            'ero-tube.org',
            'evopo.com',
            'explodemail.com',
            'express.net.ua',
            'eyepaste.com',
            'fakeinbox.com',
            'fakeinformation.com',
            'fansworldwide.de',
            'fantasymail.de',
            'fightallspam.com',
            'filzmail.com',
            'fivemail.de',
            'fleckens.hu',
            'frapmail.com',
            'friendlymail.co.uk',
            'fuckingduh.com',
            'fudgerub.com',
            'fyii.de',
            'garliclife.com',
            'gehensiemirnichtaufdensack.de',
            'get2mail.fr',
            'getairmail.com',
            'getmails.eu',
            'getonemail.com',
            'giantmail.de',
            "girlsundertheinfluence.com",
            'gishpuppy.com',
            'gmial.com',
            'givmail.com',
            'goemailgo.com',
            'gotmail.net',
            'gotmail.org',
            'gotti.otherinbox.com',
            'great-host.in',
            'greensloth.com',
            'grr.la',
            'gsrv.co.uk',
            'guerillamail.biz',
            'guerillamail.com',
            'guerrillamail.biz',
            'guerrillamail.com',
            'guerrillamail.de',
            'guerrillamail.info',
            'guerrillamail.net',
            'guerrillamail.org',
            'guerrillamailblock.com',
            'gustr.com',
            'harakirimail.com',
            'hat-geld.de',
            'hatespam.org',
            'herp.in',
            'hidemail.de',
            'hidzz.com',
            'hmamail.com',
            'hopemail.biz',
            'ieh-mail.de',
            'ikbenspamvrij.nl',
            'imails.info',
            'inbax.tk',
            'inbox.si',
            'inboxalias.com',
            'inboxbear.com',
            'inboxclean.com',
            'inboxclean.org',
            'infocom.zp.ua',
            'instant-mail.de',
            'ip6.li',
            'irish2me.com',
            'ishyp.com',
            'iwi.net',
            'jetable.com',
            'jetable.fr.nf',
            'jetable.net',
            'jetable.org',
            'jnxjn.com',
            'jourrapide.com',
            'jsrsolutions.com',
            'kasmail.com',
            'kaspop.com',
            'killmail.com',
            'killmail.net',
            'klassmaster.com',
            'klzlk.com',
            'koszmail.pl',
            'kurzepost.de',
            'lawlita.com',
            'letthemeatspam.com',
            'lhsdv.com',
            'lifebyfood.com',
            'link2mail.net',
            'lmaritimen.com',
            'litedrop.com',
            'lol.ovpn.to',
            'lolfreak.net',
            'lookugly.com',
            'lortemail.dk',
            'lr78.com',
            'lroid.com',
            'lukop.dk',
            'lutota.com',
            'm21.cc',
            'mail-filter.com',
            'mail-temporaire.fr',
            'mail.by',
            'mail.mezimages.net',
            'mail.zp.ua',
            'mail1a.de',
            'mail21.cc',
            'mail2rss.org',
            'mail333.com',
            'mailbidon.com',
            'mailbiz.biz',
            'mailblocks.com',
            'mailbucket.org',
            'mailcat.biz',
            'mailcatch.com',
            'mailde.de',
            'mailde.info',
            'maildrop.cc',
            'maileimer.de',
            'mailexpire.com',
            'mailfa.tk',
            'mailforspam.com',
            'mailfreeonline.com',
            'mailguard.me',
            'mailin8r.com',
            'mailinater.com',
            'mailinator.com',
            'mailinator.net',
            'mailinator.org',
            'mailinator2.com',
            'mailincubator.com',
            'mailismagic.com',
            'mailme.lv',
            'mailme24.com',
            'mailmetrash.com',
            'mailmoat.com',
            'mailms.com',
            'mailnesia.com',
            'mailnull.com',
            'mailorg.org',
            'mailpick.biz',
            'mailrock.biz',
            'mailscrap.com',
            'mailshell.com',
            'mailsiphon.com',
            'mailtemp.info',
            'mailtome.de',
            'mailtothis.com',
            'mailtrash.net',
            'mailtv.net',
            'mailtv.tv',
            'mailzilla.com',
            'makemetheking.com',
            'manybrain.com',
            'mega.zik.dj',
            'mbx.cc',
            'meinspamschutz.de',
            'meltmail.com',
            'messagebeamer.de',
            'mezimages.net',
            'ministry-of-silly-walks.de',
            'mintemail.com',
            'misterpinball.de',
            'moncourrier.fr.nf',
            'monemail.fr.nf',
            'monmail.fr.nf',
            'monumentmail.com',
            'mt2009.com',
            'mt2014.com',
            'mycard.net.ua',
            'mycleaninbox.net',
            'mymail-in.net',
            'mypacks.net',
            'mypartyclip.de',
            'myphantomemail.com',
            'mysamp.de',
            'mytempemail.com',
            'mytempmail.com',
            'mytrashmail.com',
            'nabuma.com',
            'neomailbox.com',
            'nepwk.com',
            'nervmich.net',
            'nervtmich.net',
            'netmails.com',
            'netmails.net',
            'neverbox.com',
            'nice-4u.com',
            'nincsmail.hu',
            'nnh.com',
            'no-spam.ws',
            'noblepioneer.com',
            'nomail.pw',
            'nomail.xl.cx',
            'nomail2me.com',
            'nomorespamemails.com',
            'nospam.ze.tc',
            'nospam4.us',
            'nospamfor.us',
            'nospammail.net',
            'notmailinator.com',
            'nowhere.org',
            'nowmymail.com',
            'nurfuerspam.de',
            'nus.edu.sg',
            'objectmail.com',
            'obobbo.com',
            'odnorazovoe.ru',
            'oneoffemail.com',
            'onewaymail.com',
            'onlatedotcom.info',
            'online.ms',
            'opayq.com',
            'ordinaryamerican.net',
            'otherinbox.com',
            'ovpn.to',
            'owlpic.com',
            'pancakemail.com',
            'pcusers.otherinbox.com',
            'pjjkp.com',
            'plexolan.de',
            'poczta.onet.pl',
            'politikerclub.de',
            'poofy.org',
            'pookmail.com',
            'privacy.net',
            'privatdemail.net',
            'proxymail.eu',
            'prtnx.com',
            'putthisinyourspamdatabase.com',
            'putthisinyourspamdatabase.com',
            'qq.com',
            'quickinbox.com',
            'rcpt.at',
            'reallymymail.com',
            'realtyalerts.ca',
            'recode.me',
            'recursor.net',
            'reliable-mail.com',
            'rhyta.com',
            'rmqkr.net',
            'royal.net',
            'rtrtr.com',
            's0ny.net',
            'safe-mail.net',
            'safersignup.de',
            'safetymail.info',
            'safetypost.de',
            'saynotospams.com',
            'schafmail.de',
            'schrott-email.de',
            'secretemail.de',
            'secure-mail.biz',
            'senseless-entertainment.com',
            'services391.com',
            'sharklasers.com',
            'shieldemail.com',
            'shiftmail.com',
            'shitmail.me',
            'shitware.nl',
            'shmeriously.com',
            'shortmail.net',
            'sibmail.com',
            'sinnlos-mail.de',
            'slapsfromlastnight.com',
            'slaskpost.se',
            'smashmail.de',
            'smellfear.com',
            'snakemail.com',
            'sneakemail.com',
            'sneakmail.de',
            'snkmail.com',
            'sofimail.com',
            'solvemail.info',
            'sogetthis.com',
            'soodonims.com',
            'spam4.me',
            'spamail.de',
            'spamarrest.com',
            'spambob.net',
            'spambog.ru',
            'spambox.us',
            'spamcannon.com',
            'spamcannon.net',
            'spamcon.org',
            'spamcorptastic.com',
            'spamcowboy.com',
            'spamcowboy.net',
            'spamcowboy.org',
            'spamday.com',
            'spamex.com',
            'spamfree.eu',
            'spamfree24.com',
            'spamfree24.de',
            'spamfree24.org',
            'spamgoes.in',
            'spamgourmet.com',
            'spamgourmet.net',
            'spamgourmet.org',
            'spamherelots.com',
            'spamherelots.com',
            'spamhereplease.com',
            'spamhereplease.com',
            'spamhole.com',
            'spamify.com',
            'spaml.de',
            'spammotel.com',
            'spamobox.com',
            'spamslicer.com',
            'spamspot.com',
            'spamthis.co.uk',
            'spamtroll.net',
            'speed.1s.fr',
            'spoofmail.de',
            'stuffmail.de',
            'super-auswahl.de',
            'supergreatmail.com',
            'supermailer.jp',
            'superrito.com',
            'superstachel.de',
            'suremail.info',
            'talkinator.com',
            'teewars.org',
            'teleworm.com',
            'teleworm.us',
            'temp-mail.org',
            'temp-mail.ru',
            'tempe-mail.com',
            'tempemail.co.za',
            'tempemail.com',
            'tempemail.net',
            'tempemail.net',
            'tempinbox.co.uk',
            'tempinbox.com',
            'tempmail.eu',
            'tempmaildemo.com',
            'tempmailer.com',
            'tempmailer.de',
            'tempomail.fr',
            'temporaryemail.net',
            'temporaryforwarding.com',
            'temporaryinbox.com',
            'temporarymailaddress.com',
            'tempthe.net',
            'thankyou2010.com',
            'thc.st',
            'thelimestones.com',
            'thisisnotmyrealemail.com',
            'thismail.net',
            'throwawayemailaddress.com',
            'tilien.com',
            'tittbit.in',
            'tizi.com',
            'tmailinator.com',
            'tmmcv.net',
            'toomail.biz',
            'topranklist.de',
            'tradermail.info',
            'trash-mail.at',
            'trash-mail.com',
            'trash-mail.de',
            'trash2009.com',
            'trashdevil.com',
            'trashemail.de',
            'trashmail.at',
            'trashmail.com',
            'trashmail.de',
            'trashmail.me',
            'trashmail.net',
            'trashmail.org',
            'trashymail.com',
            'trialmail.de',
            'trillianpro.com',
            'twinmail.de',
            'tyldd.com',
            'uggsrock.com',
            'umail.net',
            'uroid.com',
            'us.af',
            'venompen.com',
            'veryrealemail.com',
            'viditag.com',
            'viralplays.com',
            'vpn.st',
            'vomoto.com',
            'vsimcard.com',
            'vubby.com',
            'wasteland.rfc822.org',
            'webemail.me',
            'weg-werf-email.de',
            'wegwerf-emails.de',
            'wegwerfadresse.de',
            'wegwerfemail.com',
            'wegwerfemail.de',
            'wegwerfmail.de',
            'wegwerfmail.info',
            'wegwerfmail.net',
            'wegwerfmail.org',
            'wh4f.org',
            'whyspam.me',
            'willhackforfood.biz',
            'willselfdestruct.com',
            'winemaven.info',
            'wronghead.com',
            'www.e4ward.com',
            'www.mailinator.com',
            'wwwnew.eu',
            'x.ip6.li',
            'xagloo.com',
            'xemaps.com',
            'xents.com',
            'xmaily.com',
            'xoxy.net',
            'xcoxc.com',
            'yep.it',
            'yogamaven.com',
            // 'yopmail.com',
            'yopmail.fr',
            'yopmail.net',
            'yourdomain.com',
            'moc.draw4e.uoy',
            'yuurok.com',
            'z1p.biz',
            'za.com',
            'zehnminuten.de',
            'zehnminutenmail.de',
            'zippymail.info',
            'zoemail.net',
            'zomg.info'
        );

        //extract domain name from email
        // $not_allowed = ['yopmail.com', 'mailinator.com'];

        //extract domain name from email
        $domain = explode('@', $email);

        if (in_array($domain[1], $not_allowed)) {
           return 0;
        } else {
           return 1;
        }

    }

    if (!function_exists('sendFCMNotification')) {
        function sendFCMNotification(array $message, array $fcm_regid, $role)
        {

          $fields = array();

          $url = 'https://fcm.googleapis.com/fcm/send';
          $headers = array('Authorization: key=' . Config::get('constants.FCM_KEY'), 'Content-Type: application/json');

          $fields = array(
            'registration_ids' => $fcm_regid,
            'data' => $message,
            "priority" => "high",
            'notification' => array(
              "title" => $message['title'],
              "body"  =>  $message['message'],
            )
          );
          // echo json_encode($fields);exit;
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
          $result = curl_exec($ch);
          if ($result) {
            curl_close($ch);
            return true;
          }
          curl_close($ch);
          return false;
        }
    }

    function sendFCMNotifications($token, $title, $body, $data)
    {
        $client = new Client();
        $response = $client->post("https://fcm.googleapis.com/fcm/send", [
            'headers' => [
                'Authorization' => 'key=' . Config::get('constants.FCM_KEY'),
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $data
            ]
        ]);
        return $response->getBody()->getContents();
    }

    function generateInvoicePdf($invoice) {
        ini_set('memory_limit', '8G');
        $stripe = Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        $subscription = \Stripe\Subscription::Retrieve($invoice->subscription);
        $item1 = $subscription['items']['data'][0];
        $item2 = count($subscription->items->data) == 2 ? $subscription->items->data[1] : '';
        $data = [
            'name' => $invoice->customer_name ? $invoice->customer_name : '',
            'email' => $invoice->customer_email ? $invoice->customer_email : '',
            'phone' => $invoice->customer_phone ? $invoice->customer_phone : '',
            'invoice_number' => $invoice->number ? $invoice->number : '',
            'amount_paid' => $invoice->amount_paid ? $invoice->amount_paid/100 : '',
            'currency' => $invoice->currency ? $invoice->currency : '',
            'period_start' => $subscription->current_period_start ? $subscription->current_period_start : '',
            'period_end' => $subscription->current_period_end ? $subscription->current_period_end : '',
            'subtotal' => $invoice->subtotal ? $invoice->subtotal/100 : '',
            'total' => $invoice->total ? $invoice->total/100 : '',
            'item1_name' => $item1->price->nickname,
            'item1_unit_price' => $item1->price->unit_amount,
            'item1_quantity' => $item1->quantity,
            'item2_name' => $item2 ? $item2->price->nickname : '',
            'item2_quantity' => $item2 ? $item2->quantity : '',
            'item2_unit_price' => $item2 ? $item2->price->unit_amount : '',
            'item2' => count($subscription->items->data),
        ];
        $pdf = Pdf::loadView('invoice', $data);
        $pdf_name = 'invoice_'.time().'.pdf';
        $path = Storage::put('invoices/'.$pdf_name,$pdf->output());
        $invoice_url = ('storage/app/invoices/'.$pdf_name);
        Transactions::where('subscription_id', '=', $invoice->subscription)->update(['invoice_url' => $invoice_url]);
        $email = $invoice->customer_email;
        $data1 = ['salutation' => __('msg.Dear'),'name'=> $invoice->customer_name, 'msg'=> __('msg.This email serves to confirm the successful setup of your subscription with Us.'), 'msg1'=> __('msg.We are delighted to welcome you as a valued subscriber and are confident that you will enjoy the benefits of Premium Services.'),'msg2' => __('msg.Thank you for your trust!')];

        Mail::send('invoice_email', $data1, function ($message) use ($pdf_name, $email, $pdf) {
            $message->to($email)->subject('Invoice');
            $message->attachData($pdf->output(), $pdf_name, ['as' => $pdf_name, 'mime' => 'application/pdf']);
        });
        return $path;
    }

    function GetToken($user_id){
    
        $appID         =   env('APP_ID');
        $appCertificate    =   env('APP_CERTIFICATE');
        $channelName  =   (string) random_int(100000000, 9999999999999999);
        $uid = $user_id;
        $uidStr = ($user_id) . '';
        $role = RtcTokenBuilder::RolePublisher;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
    
        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
        $data = ['token' => $token, 'channel' => $channelName];
        return $data;
    
    }
?>
