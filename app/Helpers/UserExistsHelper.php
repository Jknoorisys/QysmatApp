<?php

use App\Models\Admin;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\InstantMatchRequest;
use App\Models\LastSwipe;
use App\Models\Matches;
use App\Models\MessagedUsers;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\RecievedMatches;
use App\Models\ReferredMatches;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\Transactions;
use App\Models\UnMatches;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Stripe\Stripe;
use Willywes\AgoraSDK\RtcTokenBuilder;
use App\Models\Admin as AdminModel;
use App\Notifications\MutualMatchNotification;

    function userExist($login_id, $user_type)
    {
        if ($user_type == 'singleton') {
            $user = Singleton::find($login_id);

            if (empty($user)) {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Deleted') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Blocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.blocked'),
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
            if (empty($user)) {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Deleted') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Blocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.blocked'),
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

            if (empty($user)) {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Deleted') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Blocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.blocked'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

        } elseif ($user_type == 'parent') {
            $user = ParentsModel::find($login_id);
            if (empty($user)) {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Deleted') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Blocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.blocked'),
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
            if (empty($user)) {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Deleted') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Blocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.blocked'),
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
            if (empty($user)) {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
            
            if (empty($user) || $user->status == 'Deleted') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Blocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.blocked'),
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
        DB::table('transactions')->where('subscription_id', '=', $invoice->subscription)->update(['invoice_url' => $invoice_url]);
        $email = $invoice->customer_email;
        $data1 = ['salutation' => __('msg.Dear'),'name'=> $invoice->customer_name, 'msg'=> __('msg.This email serves to confirm the successful setup of your subscription with Us.'), 'msg1'=> __('msg.We are delighted to welcome you as a valued subscriber and are confident that you will enjoy the benefits of Premium Services.'),'msg2' => __('msg.Thank you for your trust!')];

        Mail::send('invoice_email', $data1, function ($message) use ($pdf_name, $email, $pdf) {
            $message->to($email)->subject('Invoice');
            $message->replyTo('noreply@qysmat.com', 'No Reply');
            $message->attachData($pdf->output(), $pdf_name, ['as' => $pdf_name, 'mime' => 'application/pdf']);
        });
        return $path;
    }

    function GetToken($user_id, $channelName){
    
        $appID         =   env('APP_ID');
        $appCertificate    =   env('APP_CERTIFICATE');
        // $channelName  =   (string) random_int(100000000, 9999999999999999);
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

    function deleteAccountDetails($user_id, $user_type, $active_subscription_id)
    {

        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET_KEY')
        );

        if ($user_type == 'parent') {
            $parentTransaction = DB::table('transactions')
                            ->where([['user_id', '=', $user_id],['user_type', '=', $user_type],['subs_status', '!=', 'canceled']])
                            ->orWhere(function($query) use ($user_id, $user_type){
                                $query->whereRaw("FIND_IN_SET($user_id, other_user_id)")
                                    ->where('other_user_type', '=', $user_type)
                                    ->where('subs_status', '!=', 'canceled');
                            })
                            ->first();

            if (!empty($parentTransaction)) {
                if ($active_subscription_id == 2) {
                    if ($parentTransaction->payment_method == 'stripe') {
                        $parentSubscription = $stripe->subscriptions->cancel(
                            $parentTransaction->subscription_id,
                            []
                        );
        
                        if ($parentSubscription) {
                            Transactions::where('subscription_id','=', $parentSubscription->id)->update(['subs_status' => $parentSubscription->status, 'updated_at' => date('Y-m-d H:i:s')]);
                        }
                    }else{
                        Transactions::where('subscription_id','=', $parentTransaction->subscription_id)->update(['subs_status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')]);
                    }
                } elseif($active_subscription_id == 3) {
                    if (($parentTransaction->user_id == $user_id) && ($parentTransaction->user_type == $user_type)) {

                        if ($parentTransaction->payment_method == 'stripe') {
                            
                            $parentSubscription = $stripe->subscriptions->cancel(
                                $parentTransaction->subscription_id,
                                []
                            );
                
                            if ($parentSubscription) {
                                Transactions::where('subscription_id','=', $parentSubscription->id)->update(['subs_status' => $parentSubscription->status, 'updated_at' => date('Y-m-d H:i:s')]);
                                $update_sub_data = [
                                    'customer_id'            => '',
                                    'active_subscription_id' => 1,
                                    'stripe_plan_id'         => '',
                                    'subscription_item_id'   => ''
                                ];
                
                                if ($active_subscription_id == 3 && $parentTransaction->other_user_id) {
                                    $other_user_ids = $parentTransaction->other_user_id ? explode(',',$parentTransaction->other_user_id) : null;
                                    if ($parentTransaction->other_user_type == 'singleton') {
                                        foreach ($other_user_ids as $id) {
                                            Singleton::where('id','=',$id)->update($update_sub_data);
                                        }
                                    } elseif ($parentTransaction->other_user_type == 'parent') {
                                        foreach ($other_user_ids as $id) {
                                            ParentsModel::where('id','=',$id)->update($update_sub_data);
                                        }
                                    }
                                }
        
                                $linkedChild = ParentChild::where([['parent_id', '=', $user_id],['status', '=', 'Linked']])->get();
                                if (!$linkedChild->isEmpty()) {
                                    foreach ($linkedChild as $child) {
                                        $singleton_id = $child->singleton_id ;
                                        $childTransaction = DB::table('transactions')
                                                                ->where([['user_id', '=', $singleton_id],['user_type', '=', 'singleton'],['active_subscription_id','=','3']])
                                                                ->first();
                                        $childSubscription = $stripe->subscriptions->cancel(
                                                            $childTransaction->subscription_id,
                                                            []
                                                        );
                                        if ($childSubscription) {
                                            Transactions::where('subscription_id','=', $childSubscription->id)->update(['subs_status' => $childSubscription->status, 'updated_at' => date('Y-m-d H:i:s')]);
                                            $update_sub_data = [
                                                'customer_id'            => '',
                                                'active_subscription_id' => 1,
                                                'stripe_plan_id'         => '',
                                                'subscription_item_id'   => ''
                                            ];
        
                                            Singleton::where('id','=',$singleton_id)->update($update_sub_data);
                                        }
                                    }
                                }
                            }
                        } else {
                            Transactions::where('subscription_id','=', $parentTransaction->subscription_id)->update(['subs_status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')]);
                            $update_sub_data = [
                                'customer_id'            => '',
                                'active_subscription_id' => 1,
                                'stripe_plan_id'         => '',
                                'subscription_item_id'   => ''
                            ];
            
                            if ($active_subscription_id == 3 && $parentTransaction->other_user_id) {
                                $other_user_ids = $parentTransaction->other_user_id ? explode(',',$parentTransaction->other_user_id) : null;
                                if ($parentTransaction->other_user_type == 'singleton') {
                                    foreach ($other_user_ids as $id) {
                                        Singleton::where('id','=',$id)->update($update_sub_data);
                                    }
                                } elseif ($parentTransaction->other_user_type == 'parent') {
                                    foreach ($other_user_ids as $id) {
                                        ParentsModel::where('id','=',$id)->update($update_sub_data);
                                    }
                                }
                            }
    
                            $linkedChild = ParentChild::where([['parent_id', '=', $user_id],['status', '=', 'Linked']])->get();
                            if (!$linkedChild->isEmpty()) {
                                foreach ($linkedChild as $child) {
                                    $singleton_id = $child->singleton_id ;
                                    $childTransaction = DB::table('transactions')
                                                            ->where([['user_id', '=', $singleton_id],['user_type', '=', 'singleton'],['active_subscription_id','=','3']])
                                                            ->first();
                        
                                    if ($childTransaction) {
                                        Transactions::where('subscription_id','=', $childTransaction->subscription_id)->update(['subs_status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')]);
                                        $update_sub_data = [
                                            'customer_id'            => '',
                                            'active_subscription_id' => 1,
                                            'stripe_plan_id'         => '',
                                            'subscription_item_id'   => ''
                                        ];
    
                                        Singleton::where('id','=',$singleton_id)->update($update_sub_data);
                                    }
                                }
                            }
                        }
                    } 
                    // elseif (($transaction->other_user_id == $user_id) && ($transaction->other_user_type == $user_type)) {
                    //     $subscription = $stripe->subscriptions->cancel(
                    //         $transaction->subscription_id,
                    //         []
                    //     );
            
                    //     if ($subscription) {
                    //         Transactions::where('id','=', $transaction->id)->update(['subs_status' => $subscription->status, 'updated_at' => date('Y-m-d H:i:s')]);
                    //         $update_sub_data = [
                    //             'customer_id'            => '',
                    //             'active_subscription_id' => 1,
                    //             'stripe_plan_id'         => '',
                    //             'subscription_item_id'   => ''
                    //         ];
            
                            
                    //         Singleton::where('id','=',$transaction->user_id)->update($update_sub_data);
    
                    //         $linkedChild = ParentChild::where([['parent_id', '=', $user_id],['status', '=', 'Linked']])->get();
                    //         if (!$linkedChild->isEmpty()) {
                    //             foreach ($linkedChild as $child) {
                    //                 $singleton_id = $child->singleton_id ;
                    //                 $childTransaction = DB::table('transactions')
                    //                                         ->where([['user_id', '=', $singleton_id],['user_type', '=', 'singleton'],['active_subscription_id','=','3']])
                    //                                         ->first();
                    //                 $childsubscription = $stripe->subscriptions->cancel(
                    //                                     $childTransaction->subscription_id,
                    //                                     []
                    //                                 );
                    //                 if ($childsubscription) {
                    //                     Transactions::where('id','=', $childsubscription->id)->update(['subs_status' => $childsubscription->status, 'updated_at' => date('Y-m-d H:i:s')]);
                    //                     $update_sub_data = [
                    //                         'customer_id'            => '',
                    //                         'active_subscription_id' => 1,
                    //                         'stripe_plan_id'         => '',
                    //                         'subscription_item_id'   => ''
                    //                     ];
    
                    //                     Singleton::where('id','=',$singleton_id)->update($update_sub_data);
                    //                 }
                    //             }
                                
                    //         }
                    //     }
                    // }
                }
            }
        } elseif($user_type == 'singleton') {
            
            $singletonTransaction = DB::table('transactions')
                                    ->where([['user_id', '=', $user_id],['user_type', '=', $user_type],['subs_status', '!=', 'canceled']])
                                    ->orWhere(function($query) use ($user_id, $user_type){
                                        $query->whereRaw("FIND_IN_SET($user_id, other_user_id)")
                                            ->where('other_user_type', '=', $user_type)
                                            ->where('subs_status', '!=', 'canceled');
                                    })
                                    ->first();

           if (!empty($singletonTransaction)) {
                $in_other_user_ids = $singletonTransaction ? explode(',',$singletonTransaction->other_user_id) : null;
                if ($active_subscription_id == 2) {
                    if ($singletonTransaction->payment_method == 'stripe') {
                        $singletonSubscription = $stripe->subscriptions->cancel(
                            $singletonTransaction->subscription_id,
                            []
                        );
    
                        if ($singletonSubscription) {
                            Transactions::where('subscription_id','=', $singletonSubscription->id)->update(['subs_status' => $singletonSubscription->status, 'updated_at' => date('Y-m-d H:i:s')]);
                        }
                    } else {
                        Transactions::where('subscription_id','=', $singletonTransaction->subscription_id)->update(['subs_status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')]);
                    }
                    
                } elseif($active_subscription_id == 3) {
                    if (($singletonTransaction->user_id == $user_id) && ($singletonTransaction->user_type == $user_type)) {
                        if ($singletonTransaction->payment_method == 'stripe') {
                            $singletonSubscription = $stripe->subscriptions->cancel(
                                $singletonTransaction->subscription_id,
                                []
                            );
                            
                            if ($singletonSubscription) {
                                Transactions::where('subscription_id','=', $singletonSubscription->id)->update(['subs_status' => $singletonSubscription->status, 'updated_at' => date('Y-m-d H:i:s')]);
                            }
                        } else {
                            Transactions::where('subscription_id','=', $singletonTransaction->subscription_id)->update(['subs_status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')]);
                        }
                    } elseif ($in_other_user_ids && in_array($user_id, $in_other_user_ids) && ($singletonTransaction->other_user_type == $user_type)) {
                        if ($singletonTransaction->item2_quantity == 1) {
                            if ($singletonTransaction->payment_method == 'stripe') {
                                $singletonSubscription = $stripe->subscriptionItems->delete(
                                    $singletonTransaction->subscription_item2_id,
                                    []
                                  );
    
                                if ($singletonSubscription) {
                                    $updateData = [
                                        'other_user_id' => "",
                                        "other_user_type" => "",
                                        "subscription_item2_id" => "",
                                        "item2_plan_id" => "",
                                        "item2_unit_amount" => "",
                                        'item2_quantity' => "",
                                        'amount_paid' => $singletonTransaction->item1_unit_amount,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ];
    
                                    Transactions::where('subscription_item2_id','=', $singletonSubscription->id)->update($updateData);
                                }
                            } else {
                                $updateData = [
                                    'other_user_id' => "",
                                    "other_user_type" => "",
                                    "subscription_item2_id" => "",
                                    "item2_plan_id" => "",
                                    "item2_unit_amount" => "",
                                    'item2_quantity' => "",
                                    'amount_paid' => $singletonTransaction->item1_unit_amount,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ];

                                Transactions::where('subscription_id','=', $singletonTransaction->subscription_id)->update($updateData);
                            }
                            
                        } elseif($singletonTransaction->item2_quantity > 1) {
                            $quantity = $singletonTransaction->item2_quantity - 1;
                            if ($singletonTransaction->payment_method == 'stripe') {
                                $singletonSubscription = $stripe->subscriptionItems->update(
                                    $singletonTransaction->subscription_item2_id,
                                    ['quantity' => $quantity]
                                );

                                if ($singletonSubscription) {
                                    $updateData = [
                                        'other_user_id' => str_replace($user_id, "", $singletonTransaction->other_user_id),
                                        'item2_quantity' => $singletonSubscription->quantity,
                                        'amount_paid' => $singletonTransaction->amount_paid - $singletonTransaction->item2_unit_amount ,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ];

                                    Transactions::where('subscription_item2_id','=', $singletonSubscription->id)->update($updateData);
                                }
                            } else {
                                $updateData = [
                                    'other_user_id' => str_replace($user_id, "", $singletonTransaction->other_user_id),
                                    'item2_quantity' => $quantity,
                                    'amount_paid' => $singletonTransaction->amount_paid - $singletonTransaction->item2_unit_amount ,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ];

                                Transactions::where('subscription_id','=', $singletonTransaction->subscription_id)->update($updateData);
                            }
                            
                        }
                        
                    }
                }
           }
        }
            
        if($user_type == 'singleton'){
            $link = ParentChild::where([['singleton_id', '=', $user_id]])->delete();
        }else{
            // $link = ParentChild::where([['parent_id', '=', $user_id]])->delete();
            $link = ParentChild::where([['parent_id', '=', $user_id]])->update(['parent_id' => 0, 'status' => 'Unlinked', 'updated_at' => date('Y-m-d H:i:s')]);
            if($link){
                Singleton::where('parent_id', '=', $user_id)->update(['parent_id' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }

        if ($user_type == 'parent') {
            $match = MyMatches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                            ->delete();

            $unmatch = UnMatches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                            ->delete();

            $refer = ReferredMatches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                    ->delete();

            $received = RecievedMatches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                       ->delete();

            $requests = InstantMatchRequest::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                            ->orWhere([['requested_parent_id','=',$user_id],['user_type','=',$user_type]])
                                            ->delete();

        } else {
            $match = MyMatches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                            ->orWhere('matched_id','=',$user_id)
                            ->delete();

            $unmatch = UnMatches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                            ->orWhere('un_matched_id','=',$user_id)
                            ->delete();

            $refer = ReferredMatches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                    ->orWhere('referred_match_id','=',$user_id)
                                    ->delete();

            $received = RecievedMatches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                    ->orWhere('recieved_match_id','=',$user_id)
                                    ->delete();

            $requests = InstantMatchRequest::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                    ->orWhere([['requested_id','=',$user_id],['user_type','=',$user_type]])
                                    ->delete();

        }

        
        $block = BlockList::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                            ->orWhere([['blocked_user_id','=',$user_id],['blocked_user_type','=',$user_type]])
                            ->delete();

        $report = ReportedUsers::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                ->orWhere([['reported_user_id','=',$user_id],['reported_user_type','=',$user_type]])
                                ->delete();

        $chat = MessagedUsers::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                ->orWhere([['messaged_user_id','=',$user_id],['messaged_user_type','=',$user_type]])
                                ->delete();

        $chat = ChatHistory::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                            ->orWhere([['messaged_user_id','=',$user_id],['messaged_user_type','=',$user_type]])->delete();

        $swipe = LastSwipe::where([['user_id','=',$user_id],['user_type','=',$user_type]])->delete();

        if ($user_type == 'singleton') {
            $mutual = Matches::where([['user_id','=',$user_id],['user_type','=',$user_type], ['match_type', '=', 'hold']])
                                ->orWhere([['match_id','=',$user_id],['user_type','=','singleton'], ['match_type', '=', 'hold']])
                                ->update(['match_type' => 'liked', 'queue' => 0, 'is_rematched' => 'no', 'Matched_at' => Null]);
            $liked = Matches::where([['user_id','=',$user_id],['user_type','=',$user_type], ['match_type', '=', 'liked']])
                                ->orWhere([['user_id','=',$user_id],['user_type','=',$user_type], ['match_type', '=', 'un-matched']])
                                ->delete();

            $matched = Matches::where([['user_id','=',$user_id],['user_type','=',$user_type], ['match_type', '=', 'matched']])
                                ->orWhere([['match_id','=',$user_id],['user_type','=','singleton'], ['match_type', '=', 'matched']])
                                ->first();

            if (!empty($matched)) {
                $matched->match_id != $user_id ? $un_matched_id = $matched->match_id : $un_matched_id = $matched->user_id;

                $other_queue = Matches::leftjoin('singletons', function($join) use ($un_matched_id) {
                                            $join->on('singletons.id','=','matches.match_id')
                                                ->where('matches.match_id','!=',$un_matched_id);
                                            $join->orOn('singletons.id','=','matches.user_id')
                                                ->where('matches.user_id','!=',$un_matched_id);
                                        })
                                        ->where('singletons.chat_status', '=','available')
                                        ->where(function($query) use ($un_matched_id){
                                            $query->where([['matches.user_id', '=', $un_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']])
                                                ->orWhere([['matches.match_id', '=', $un_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']]);
                                        })
                                        ->orderBy('matches.queue')->first(['matches.*']);

                if (!empty($other_queue)) {
                    Matches::where([['user_id', '=', $un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'hold'], ['queue', '=', $other_queue->queue]])
                                    ->orWhere([['match_id', '=', $un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'hold'], ['queue', '=', $other_queue->queue]])
                                    ->update(['match_type' => 'matched','queue' => 0, 'matched_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);

                    $notify = Matches::where([['user_id', '=', $un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                            ->orWhere([['match_id', '=', $un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                            ->first();
                    if (!empty($notify)) {
                        // send congratulations fcm notification
                        $user2 = Singleton::whereId($notify->user_id)->first();
                        $user1 = Singleton::whereId($notify->match_id)->first();

                        if (isset($user1) && !empty($user1) && isset($user2) && !empty($user2)) {
                            $title = __('msg.Profile Matched');
                            $body = __('msg.Congratulations It’s a Match!');

                            // database notification
                            $msg = __('msg.Congratulations! You got a new match with');
                            $user2->notify(new MutualMatchNotification($user1, $user2->user_type, 0, ($msg.' '.$user1->name)));
                            $user1->notify(new MutualMatchNotification($user2, $user1->user_type, 0, ($msg.' '.$user2->name)));

                            $token1 = $user1->fcm_token;
                            $data = array(
                                'notType' => "profile_matched",
                                'user1_id' => $user1->id,
                                'user1_name' => $user1->name,
                                'user1_profile' => $user1->photo1,
                                'user1_blur_image' => ($user1->gender == 'Female' ? $notify->blur_image : 'no'),
                                'user2_id' => $user2->id,
                                'user2_name' => $user2->name,
                                'user2_profile' => $user2->photo1,
                                'user2_blur_image' => ($user2->gender == 'Female' ? $notify->blur_image : 'no'),
                            );
                            sendFCMNotifications($token1, $title, $body, $data);

                            $token2 = $user2->fcm_token;
                            $data1 = array(
                                'notType' => "profile_matched",
                                'user1_id' => $user2->id,
                                'user1_name' => $user2->name,
                                'user1_profile' => $user2->photo1,
                                'user1_blur_image' => ($user2->gender == 'Female' ? $notify->blur_image : 'no'),
                                'user2_id' => $user1->id,
                                'user2_name' => $user1->name,
                                'user2_profile' => $user1->photo1,
                                'user2_blur_image' => ($user1->gender == 'Female' ? $notify->blur_image : 'no'),
                            );
                            sendFCMNotifications($token2, $title, $body, $data1);
                        }
                    }    

                    Matches::where([['user_id','=',$user_id],['user_type','=',$user_type], ['match_type', '=', 'matched']])
                                ->orWhere([['match_id','=',$user_id],['user_type','=','singleton'], ['match_type', '=', 'matched']])
                                ->update(['match_type' => 'liked', 'queue' => 0, 'is_rematched' => 'no', 'matched_at' => Null,'status' => 'available']);
                    Singleton::where('id', '=', $user_id)->orWhere('id', '=', $un_matched_id)->update(['chat_status' => 'available']);
                }
            }

             // delete all matches of the deleted user
             Matches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                        ->orWhere([['match_id','=',$user_id],['user_type','=','singleton']])
                        ->delete();
        } else {
            $mutual = Matches::where([['user_id','=',$user_id],['user_type','=',$user_type], ['match_type', '=', 'matched']])
                                ->orWhere([['matched_parent_id','=',$user_id],['user_type','=','parent'], ['match_type', '=', 'matched']])
                                ->update(['match_type' => 'liked', 'matched_at' => Null, 'queue' => 0, 'is_rematched' => 'no']);
            $liked = Matches::where([['user_id','=',$user_id],['user_type','=',$user_type], ['match_type', '=', 'liked']])
                                ->orWhere([['user_id','=',$user_id],['user_type','=',$user_type], ['match_type', '=', 'un-matched']])
                                ->delete();

            // delete all matches of the deleted user
            Matches::where([['user_id','=',$user_id],['user_type','=',$user_type]])
                                ->orWhere([['matched_parent_id','=',$user_id],['user_type','=','parent']])
                                ->delete();
        }
    }

    function convertFeetToInches($feet)
    {
        $parts = explode(".", $feet);
        $feetValue = intval($parts[0]) ? intval($parts[0]) : 0;
        $inchValue = count($parts) == 2 ? (intval($parts[1]) ? intval($parts[1]) : 0) : 0;
        $inches = ($feetValue * 12) + $inchValue;
        return $inches;
    }

    function sendWebNotification($title, $message)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $FcmToken = AdminModel::whereNotNull('device_token')->pluck('device_token')->all();
            
        $serverKey = 'AAAATICgKX0:APA91bHhUj1lJ3o_AX9PkU-il3O-qpZ8O2U7KvKv6nRD4xdjTVkQZBbrhXrhWMDgp6WxDvAG7rXqQb0wh8RUsZX8FO5dcLvgnbRRc343cxgEo8nA_MQkIcM08xK58qUadkAjrj_TBkaR'; // ADD SERVER KEY HERE PROVIDED BY FCM
    
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $title,
                "body" => $message,  
            ]
        ];
        $encodedData = json_encode($data);
    
        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }        
        // Close connection
        curl_close($ch);

    }
?>
