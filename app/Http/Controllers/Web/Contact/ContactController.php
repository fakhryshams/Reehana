<?php

namespace App\Http\Controllers\Web\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\AdminModel\AdminContactUs;
use App\AdminModel\GeneralSetting\AboutUs\AboutUs;
use App\AdminModel\GeneralSetting\AboutUs\AboutUsDescription;
use App\AdminModel\GeneralSetting\ShippingPolicy\ShippingPolicy;
use App\AdminModel\GeneralSetting\ReturnPolicy\AdminReturnPolicy;
use App\AdminModel\GeneralSetting\Media\AdminMedia;
use App\AdminModel\GeneralSetting\ContactUs\ContactUs;
use App\Helpers\Helper;

class ContactController extends Controller
{

    private $module   = '.contact';
    private $langFile = 'web';

    /**
     * Show the contact.
     */
    public function index()
    {
        $page = ContactUs::first();
        if ($page)
        {
            $language = Helper::getLanguage();
            $pageDesc = $page->getContactUsEditDescription($language->id);
            if ($pageDesc)
            {
                $query = config('custom_web.query') ? config('custom_web.query') : [];
                return view('web.contact.index', compact('page', 'pageDesc', 'query'));
            }
        }
        return redirect()->route('web.index')->with('error', __($this->langFile . '.error-message'));
    }

    public function refereshCapcha()
    {
        return captcha_img('math');
    }

    public function contactUs(Request $request)
    {
        $validate = $request->validate([
            'email'      => 'required|min:6',
            'first_name' => 'required',
            'last_name'  => 'required',
            'mobile'     => 'required',
            //  'sel_query'  => 'required',
            'message'    => 'required',
            'captcha'    => 'required|captcha'
                ], [
            'email.required'      => Lang::get($this->langFile . '.error-req-email'),
            'email.min'           => Lang::get($this->langFile . '.error-req-email-min'),
            'first_name.required' => Lang::get($this->langFile . '.error-req-first-name'),
            'last_name.required'  => Lang::get($this->langFile . '.error-req-last-name'),
            'mobile.required'     => Lang::get($this->langFile . '.error-req-mobile'),
            'sel_query.required'  => Lang::get($this->langFile . '.error-req-query'),
            'message.required'    => Lang::get($this->langFile . '.error-req-message'),
            'captcha.required'    => Lang::get($this->langFile . '.error-req-captcha'),
            'captcha.captcha'     => Lang::get($this->langFile . '.error-invalid-captcha'),
        ]);

        $adminContactUs             = new AdminContactUs();
        $adminContactUs->first_name = $request->first_name;
        $adminContactUs->email      = $request->email;
        $adminContactUs->last_name  = $request->last_name;
        $adminContactUs->mobile     = $request->mobile;
//        $adminContactUs->subject = $request->sel_query;
        $adminContactUs->message    = $request->message;
        if ($adminContactUs->save())
        {
            $this->sendMail($adminContactUs);
            return redirect()->route('web.contact')->with('success', Lang::get($this->langFile . '.contact-us-sussess-message'));
        }
        else
        {
            return redirect()->route('web.contact')->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    /**
     * Show the shipping.
     */
    public function shipping()
    {
        $shippingPolicy = ShippingPolicy::first();
        return view('web.page.shipping.index', compact('shippingPolicy'));
    }

    public function privacyPolicy()
    {
        $shippingPolicy = ShippingPolicy::find(2);
        return view('web.page.privacy-policy.index', compact('shippingPolicy'));
    }

    public function Terms()
    {
        $shippingPolicy = ShippingPolicy::find(3);
        return view('web.page.terms.index', compact('shippingPolicy'));
    }

    /**
     * Return.
     */
    public function returns()
    {
        $returnPolicy = AdminReturnPolicy::first();
        return view('web.return-policy.index', compact('returnPolicy'));
    }

    /**
     * Media.
     */
    public function media()
    {
        $media = AdminMedia::getRecord();
        return view('web.media.index', compact('media'));
    }

    /**
     * Show the about.
     */
    public function about()
    {
        $aboutUs = AboutUs::first();
        return view('web.about.index', compact('aboutUs'));
    }

    public function sendMail($detail)
    {
        $contactEmail     = \App\Helpers\Helper::getSettingById(19);
        $reciverEmail     = \App\Helpers\Helper::getSettingById(18);
        $app_name         = \App\Helpers\Helper::getAppName();
        $app_name         = \App\Helpers\Helper::getAppName();
        $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";
        $reciveEmail      = \App\Helpers\Helper::getSettingById(18);
        $reciveAdminEmail = $reciveEmail ? $reciveEmail->setting_val : "";

        $from['email'] = $admin_mail;
        $from['name']  = ucfirst($detail->first_name) . ' ' . ucfirst($detail->last_name);
        $to['email']   = $reciveAdminEmail;
        $to['name']    = $app_name;

        $subject      = $app_name . "-" . Lang::get($this->langFile . '.contact_us');
        $message      = \View::make('web.emails.contact_us.index', compact('detail'))->render();
        $phpMail      = new \App\Mail\CommonMailSender();
        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false);
        if ($mailResponse['status'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}
