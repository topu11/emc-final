<?php

namespace App\Services;


use App\Models\CrpcSection;
use App\Models\Upazila;
use App\Models\EmCaseShortdecisionTemplates;
use App\Repositories\AppealRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ShortOrderTemplateService
{
    public static function getShortOrderTemplateListByAppealId($appealId){
        $shortOrderTemplateList=DB::connection('mysql')
            ->table('em_cause_lists')
            ->join('em_case_shortdecision_templates', 'em_cause_lists.id', '=', 'em_case_shortdecision_templates.cause_list_id')
            ->where('em_case_shortdecision_templates.appeal_id',$appealId )
            ->select('em_case_shortdecision_templates.id','em_case_shortdecision_templates.appeal_id',
                'em_case_shortdecision_templates.template_name','em_case_shortdecision_templates.template_full',
                'em_cause_lists.trial_date','em_cause_lists.conduct_date')
            ->get();

        $index = 1;
        $templateList =  array ();

        foreach ($shortOrderTemplateList as $shortOrderTemplate)
        {
            $template['index'] = DataConversionService::toBangla($index);
            $template['appeal_id'] = $shortOrderTemplate->appeal_id;
            $template['id'] = $shortOrderTemplate->id;
            $template['template_name'] = $shortOrderTemplate->template_name;
            $template['conduct_date'] = DataConversionService::toBangla(date('d-m-Y',strtotime($shortOrderTemplate->conduct_date)));
            $template['template_full'] = $shortOrderTemplate->template_full;

            $index++;
            array_push($templateList, $template);
        }
        // dd($templateList);
        return $templateList;
    }

    public static function deleteShortOrderTemplate($causeListId){
        $shortOrderList=EmCaseShortdecisionTemplates::where('cause_list_id',$causeListId);
        // dd($shortOrderList);
        $shortOrderList->delete();
        return;
    }

    public static function storeShortOrderTemplate($shortOrderId,$appealId,$causeListId,$shortOrderTemplateContent,$templateName){
        $shortOrderTemplate=new EmCaseShortdecisionTemplates();
        $shortOrderTemplate->appeal_id=$appealId;
        $shortOrderTemplate->cause_list_id=$causeListId;
        $shortOrderTemplate->case_shortdecision_id=$shortOrderId;
        $shortOrderTemplate->template_full=$shortOrderTemplateContent;
        $shortOrderTemplate->template_header='';
        $shortOrderTemplate->template_body='';
        $shortOrderTemplate->template_name=$templateName;
        $shortOrderTemplate->created_at=date('Y-m-d H:i:s');
        $shortOrderTemplate->created_by=Auth::user()->username;
        $shortOrderTemplate->updated_at=date('Y-m-d H:i:s');
        $shortOrderTemplate->updated_by=Auth::user()->username;
        $shortOrderTemplate->save();
    }
    public static function generateShortOrderTemplate($shortOrders,$appealId,$causeList,$requestInfo){
        $appealInfo=AppealRepository::getAllAppealInfo($appealId);
        self::deleteShortOrderTemplate($causeList->id);
        // if(count($shortOrders)>0){
        if( $shortOrders != null){
        // dd($shortOrders);
            foreach ($shortOrders AS $shortOrder){

                //-------------------সমন জারী---------------------------//
                if($shortOrder==5){
                    $templateName="তদন্ত";
                    $shortOrderTemplate=self::getinvestigationRequestShortOrderTemplate($appealInfo,$causeList,5,$requestInfo);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                if($shortOrder==2){
                    $templateName="প্রসিডিং পূর্ব ইনকোয়ারি";
                    $shortOrderTemplate=self::getinvestigationRequestShortOrderTemplate($appealInfo,$causeList,2,$requestInfo);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                if($shortOrder==21){
                    $templateName="প্রাথমিক তদন্ত";
                    $shortOrderTemplate=self::getinvestigationRequestShortOrderTemplate($appealInfo,$causeList,21,$requestInfo);
                    // dd($shortOrderTemplate);
                    
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                //-------------------সমন জারী---------------------------//
                if($shortOrder==7){
                    $templateName="সমন জারী";
                    $shortOrderTemplate=self::getSommonRequestShortOrderTemplate($appealInfo,$causeList);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                //-------------------স্বাক্ষীর প্রতি সমন জারী---------------------------//
                if($shortOrder==8){
                    $templateName="স্বাক্ষীর প্রতি সমন জারী";
                    $shortOrderTemplate=self::getWitnesSommonRequestShortOrderTemplate($appealInfo,$causeList);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                //-------------------অন্তর্বর্তীকালীন মুচলেকা---------------------------//
                if($shortOrder==9){
                    $templateName="অন্তর্বর্তীকালীন মুচলেকা";
                    $shortOrderTemplate=self::getInterimBondRequestShortOrderTemplate($appealInfo,$causeList);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                //-------------------শুনানি---------------------------//
                if($shortOrder==10){
                    $templateName="শুনানি";
                    $shortOrderTemplate=self::getHearingRequestShortOrderTemplate($appealInfo,$causeList);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                //-------------------গ্রেফতারী পরোয়ানা---------------------------//
                if($shortOrder==19){
                    $templateName="গ্রেফতারী পরোয়ানা";
                    $shortOrderTemplate=self::getArrestWarrentShortOrderTemplate($appealInfo,$causeList);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                //-------------------শান্তিরক্ষার মুচলেকা---------------------------//
                if($shortOrder==20){
                    $templateName="শান্তি রক্ষার মুচলেকা";
                    $shortOrderTemplate=self::getPeaceBondShortOrderTemplate($appealInfo,$causeList);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }

                //-------------------কারণ দর্শানো নোটিশ---------------------------//
                if($shortOrder==22){
                    $templateName="কারণ দর্শানো নোটিশ";
                    $shortOrderTemplate=self::getShowCauseShortOrderTemplate($appealInfo,$causeList);
                    // dd($shortOrderTemplate);
                    self::storeShortOrderTemplate($shortOrder,$appealId,$causeList->id,$shortOrderTemplate,$templateName);
                }


            }
        }

    }

    //----------- তদন্ত -----------------------------------------//
    public static function getInvestigationRequestShortOrderTemplate($appealInfo,$causeList,$shortOrder,$requestInfo){
        $law_section = CrpcSection::where('id',$appealInfo['appeal']->law_section)->select('crpc_id')->first()->crpc_id;
        $upazila_name_bn= Upazila::where('id',$appealInfo['appeal']->upazila_id)->select('upazila_name_bn')->first()->upazila_name_bn;
        $location= $appealInfo['appeal']->office_name;
        $case_date= $appealInfo['appeal']->case_date;
        $defaulter=$appealInfo['defaulterCitizen'];
        $guarantorCitizen=$appealInfo['guarantorCitizen'];
        $case_details=  str_replace('&nbsp;', '', strip_tags($appealInfo['appeal']->case_details));
        // dd($case_details);
        $loanAmountBng=DataConversionService::toBangla($appealInfo['appeal']->loan_amount);

        $trialBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($causeList->trial_date)));
        $trialBanglaDay=DataConversionService::toBangla(date('d',strtotime($causeList->trial_date)));
        $trialBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($causeList->trial_date)));
        $trialTime=date('h:i:s a',strtotime($causeList->trial_time));
        $trialBanglaYear=DataConversionService::toBangla(date("Y", strtotime($causeList->trial_date)));

        $caseBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($case_date)));
        $caseBanglaDay=DataConversionService::toBangla(date('d',strtotime($case_date)));
        $caseBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($case_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseBanglaYear=DataConversionService::toBangla(date("Y", strtotime($case_date)));
        $title_short_order_name='';
        if($shortOrder == 2)
        {
            $title_short_order_name='প্রসিডিং পূর্ব ইনকোয়ারি এর';
        }
        elseif($shortOrder == 5)
        {
            $title_short_order_name='তদন্ত এর';
        }
        else
        {
            $title_short_order_name='প্রাথমিক তদন্ত এর';
        }
        $template='
                    <style>
                        table, th, td {
                            border: 1px solid black;
                            border-collapse: collapse;
                            padding: 10px;
                            font-weight: normal;
                        }
                    </style>
            <div style="text-align: center">
                <span style="font-size: medium;text-align: center;">এক্সিকিউটিভ ম্যাজিস্ট্রেট কোর্ট </span>
            </div>
            <div style="text-align: right">
                <span style="font-size: medium;">স্মারক নং.................. </span>
            </div>
            <div style="text-align: left">
                <span style="font-size: medium;">তারিখ '.$trialBanglaDate.'.</span>
            </div>
                        <header>
                            <div style="text-align: center">
                                <h2>'.$title_short_order_name.' জন্য নোটিশ</h2> <br>
                            </div>
                        </header>
             <br>

             <br>
             <br>
             <div style="height:100%; padding: 10px;">
                
                <span style="float: left;">
                  জনাব '.$requestInfo->investigatorName.', '.$requestInfo->investigatorDesignation.', '.$requestInfo->investigatorInstituteName.' সমীপে-
                </span>
                <br><br><br><br><br>
                
                 <span style="float: left;">যেহেতু আমার নিকট সংবাদ দেওয়া হইয়াছে যে, 
                                            '.$case_details.' , অপরাধ সংঘটিত হইয়াছে (বা সংঘটিত হইয়াছে বলিয়া সন্দেহ করা হইতেছে)
                                             এবং আমার নিকট প্রতীয়মান হইয়াছে যে,অপরাধটি (বা সন্দেহকৃত অপরাধটি) সম্পর্কে এক্ষণে বিস্তারিত তদন্ত অত্যাবশ্যক;       
                                            সেহেতু-এতদ্বারা আপনাকে ক্ষমতা ও নির্দেশ দেওয়া যাইতেছে যে,আপনি , '.user_district_name().' জেলার '.$defaulter[0]->citizen_name .', নামে যে নালিশ করা হইয়াছে, এর জন্য তদন্ত চালাইবেন এবং উহার ফলাফল প্রতিবেদন আকারে  এই আদালতে হাজির করিবেন।


                 </span>
                <br><br><br>
                <br>
                <p>তারিখঃ'.$trialBanglaYear.' সালের '.$trialBanlaMonth .' মাসের '. $trialBanglaDay.' তারিখ। <br><br><br>
                    </p>
               
                 <p style=" text-align : left; color: blueviolet;">
                        <img src="'.globalUserInfo()->signature.'" alt="signature" width="100" height="50">

                        <br>'.'<b>'. globalUserInfo()->name .'</b>'.'<br> '.   globalUserRoleInfo()->role_name .', '.user_district_name().'
                    </p>
            </div>';
        // dd($template);
        return $template;
    }

    

    //----------- সমন জারী -----------------------------------------//
    public static function getSommonRequestShortOrderTemplate($appealInfo,$causeList){
        $law_section = CrpcSection::where('id',$appealInfo['appeal']->law_section)->select('crpc_id')->first()->crpc_id;
        $location= $appealInfo['appeal']->office_name;
        $case_date= $appealInfo['appeal']->case_date;
        $defaulter=$appealInfo['defaulterCitizen'];
        $guarantorCitizen=$appealInfo['guarantorCitizen'];
        $case_details=  str_replace('&nbsp;', '', strip_tags($appealInfo['appeal']->case_details));
        // dd($case_details);
        $loanAmountBng=DataConversionService::toBangla($appealInfo['appeal']->loan_amount);

        $trialBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($causeList->trial_date)));
        $trialBanglaDay=DataConversionService::toBangla(date('d',strtotime($causeList->trial_date)));
        $trialBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($causeList->trial_date)));
        $trialTime=date('h:i:s a',strtotime($causeList->trial_time));
        $trialBanglaYear=DataConversionService::toBangla(date("Y", strtotime($causeList->trial_date)));

        $caseBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($case_date)));
        $caseBanglaDay=DataConversionService::toBangla(date('d',strtotime($case_date)));
        $caseBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($case_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseBanglaYear=DataConversionService::toBangla(date("Y", strtotime($case_date)));

        $template='
                    <style>
                        table, th, td {
                            border: 1px solid black;
                            border-collapse: collapse;
                            padding: 10px;
                            font-weight: normal;
                        }
                    </style>
            <div >
                <span style="font-size:  medium;">বাংলাদেশ ফরম নং ৩৯০৩ <br> হাইকোর্ট ফৌজদারী পরোয়ানা ফরম নং ১বি  </span>
                        <header>
                            <div style="text-align: center">
                                <h2>আসামীর প্রতি সমন</h2> <br>
                    <h5>(১৮৯৮ সালের ৫নং আইনের ৫নং তফ্সিল, ১ নম্বর ফরম)</h5><br>
                    <span>[ফৌজদারী কার্যবিধির '.en2bn($law_section).' ধারা]</span>

                            </div>
                        </header>
             <br>

             <br>
             <br>
            <div style="height:100%">

                <div>
                    <p> প্রাপক: <br>
                    নাম '.$defaulter[0]->citizen_name. ' পিতা '.$defaulter[0]->father. ' ঠিকানা '.$defaulter[0]->present_address .' <br><br><br> 
                    যেহেতু আপনার বিরুদ্ধেফৌজদারী কার্যবিধির '.en2bn($law_section).' ধারার অভিযোগের অভিযোগ অসিয়াছে, সেহেতু উহার উত্তর প্রদানের জন্য আপনি '.$trialBanglaYear.' সালের '.$trialBanlaMonth .' মাসের '. $trialBanglaDay.' তারিখে স্বয়ং অথবা উকিলের মাধ্যমে আমার সমক্ষে হাজির হইবেন। ইহার যেন অন্যথা না হয়। 
                      </p>
                </div>
                <div>
                    <p>অদ্য '.$trialBanglaYear.' সালের '.$trialBanlaMonth .' মাসের '. $trialBanglaDay.' তারিখ। <br><br><br>
                    </p>
                    <p style=" text-align : left; color: blueviolet;">
                        <img src="'.globalUserInfo()->signature.'" alt="signature" width="100" height="50">

                        <br>'.'<b>'. globalUserInfo()->name .'</b>'.'<br> '.   globalUserRoleInfo()->role_name .'
                    </p>

                </div>
            </div>';
        // dd($template);
        return $template;
    }

    //----------- স্বাক্ষীর প্রতি সমন জারী -----------------------------------------//
    public static function getWitnesSommonRequestShortOrderTemplate($appealInfo,$causeList){
        // dd($appealInfo);
        $location= $appealInfo['appeal']->office_name;
        $case_date= $appealInfo['appeal']->case_date;
        $defaulter=$appealInfo['defaulterCitizen'];
        $witness=$appealInfo['witnessCitizen'];
        $guarantorCitizen=$appealInfo['guarantorCitizen'];
        $case_details=  str_replace('&nbsp;', '', strip_tags($appealInfo['appeal']->case_details));
        $loanAmountBng=DataConversionService::toBangla($appealInfo['appeal']->loan_amount);

        $trialBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($causeList->trial_date)));
        $trialBanglaDay=DataConversionService::toBangla(date('d',strtotime($causeList->trial_date)));
        $trialBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($causeList->trial_date)));
        $trialTime=date('h:i:s',strtotime($causeList->trial_time));
        $trialTimeAmPm=date('a',strtotime($causeList->trial_time));
        $time='';
        if($trialTimeAmPm == 'am'){
            $time='সকাল';
        }else{
            $time='বিকাল';
        }
        $trialBanglaYear=DataConversionService::toBangla(date("Y", strtotime($causeList->trial_date)));

        $caseBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($case_date)));
        $caseBanglaDay=DataConversionService::toBangla(date('d',strtotime($case_date)));
        $caseBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($case_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseBanglaYear=DataConversionService::toBangla(date("Y", strtotime($case_date)));

        $template='
                    <style>
                        table, th, td {
                            border: 1px solid black;
                            border-collapse: collapse;
                            padding: 10px;
                            font-weight: normal;
                        }
                    </style>
            <div >
                <span style="font-size:  medium;">বাংলাদেশ ফরম নং ১০২৮  </span>
                        <header>
                            <div style="text-align: center">
                                <h3>স্বাক্ষীর প্রতি সমন</h3>

                            </div>
                        </header>
             <br>

             <br>
             <br>
            <div style="height:100%">
                <div>
                    <p>'.$witness[0]->citizen_name. ' , '.$witness[0]->present_address .'   সমীপে</p>
                </div>
                <div>
                    <p> যেহেতু আমার নিকট নালিশ করা হইয়াছে '.$defaulter[0]->citizen_name. ' ঠিকানা '.$defaulter[0]->present_address .' স্থানে '.$case_date.' দিবসে '.$case_details.'
                       অপরাধ করিয়াছে (বা করিয়াছে বলিয়া সন্দেহ করা হইতেছে) এবং আমার নিকট
                        প্রতীয়মান হইতেছে, আপনি সরকার পক্ষে গুরুত্বপূর্ণ সাক্ষ্য দিতে পারেন; সেহেতু</p>
                        <p>এতদ্বারা আপনাকে সমন দেওয়া যাইতেছে, আপনি '.$trialBanglaDate.'  তারিখে '.$time.' '.en2bn($trialTime).' ঘটিকায় এই আদালতে হাজির
                            হইয়া উক্ত নালিশ সম্পর্কে আপনি যাহা জানেন সেই সম্পর্কে সাক্ষ্য দিবেন, এবং আদালতের
                            অনুমতি ছাড়া আদালত ত্যাগ করিবেন না ; এবং এতদ্বারা আপনাকে সতর্ক করিয়া দেওয়া যাইতেছে
                            যে, আপনি যদি সঙ্গত কারন ব্যাতীত উক্ত তারিখে হাজির হইতে অবহেলা বা অস্বীকার করেন, তাহা
                            হইলে আপনাকে হাজির করিতে বাধ্য করিবার জন্য পরোয়ানা জারি করা হইবে।</p>

                </div>

                <div>
                    <p>তারিখঃ'.$trialBanglaYear.' সালের '.$trialBanlaMonth .' মাসের '. $trialBanglaDay.' তারিখ। <br><br><br>
                    </p>
                    <p style=" text-align : left; color: blueviolet;">
                        <img src="'.globalUserInfo()->signature.'" alt="signature" width="100" height="50">

                        <br>'.'<b>'. globalUserInfo()->name .'</b>'.'<br> '.   globalUserRoleInfo()->role_name .'
                    </p>

                </div>
            </div>';
        // dd($template);
        return $template;
    }



    //----------- অন্তর্বর্তীকালীন মুচলেকা -----------------------------------------//
    public static function getInterimBondRequestShortOrderTemplate($appealInfo,$causeList){
        // dd($appealInfo);
        $location= $appealInfo['appeal']->office_name;
        $case_date= $appealInfo['appeal']->case_date;
        $applicant=$appealInfo['applicantCitizen'];
        $defaulter=$appealInfo['defaulterCitizen'];
        $witness=$appealInfo['witnessCitizen'];
        $guarantorCitizen=$appealInfo['guarantorCitizen'];
        $case_details=  str_replace('&nbsp;', '', strip_tags($appealInfo['appeal']->case_details));
        $loanAmountBng=DataConversionService::toBangla($appealInfo['appeal']->loan_amount);

        $trialBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($causeList->trial_date)));
        $trialBanglaDay=DataConversionService::toBangla(date('d',strtotime($causeList->trial_date)));
        $trialBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($causeList->trial_date)));
        $trialTime=date('h:i:s',strtotime($causeList->trial_time));
        $trialTimeAmPm=date('a',strtotime($causeList->trial_time));
        $time='';
        if($trialTimeAmPm == 'am'){
            $time='সকাল';
        }else{
            $time='বিকাল';
        }
        $trialBanglaYear=DataConversionService::toBangla(date("Y", strtotime($causeList->trial_date)));

        $caseBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($case_date)));
        $caseBanglaDay=DataConversionService::toBangla(date('d',strtotime($case_date)));
        $caseBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($case_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseBanglaYear=DataConversionService::toBangla(date("Y", strtotime($case_date)));

        $template='
                    <style>
                        table, th, td {
                            border: 1px solid black;
                            border-collapse: collapse;
                            padding: 10px;
                            font-weight: normal;
                        }
                    </style>
            <div >
                <span style="font-size:  medium;">বাংলাদেশ ফরম নং ১০২৮  </span>
                        <header>
                            <div style="text-align: center">
                                <h3>অন্তর্বর্তীকালীন মুচলেকা</h3>

                            </div>
                        </header>
             <br>

             <br>
             <br>
            <div style="height:100%">

                <p>যেহেতু '.$applicant[0]->present_address .' এর অধিবাসী আমাকে '.$defaulter[0]->citizen_name.' '.en2bn($trialTime). '  সময়ের জন্য (অথবা আদালতে
                     এক্ষুণে '.$case_details.' বিষয় সম্পর্কে যে অনুসন্ধান চলিতেছে তাহা সমাপ্ত না হওয়া পর্যন্ত) বাংলাদেশ
                     সরকার ও বাংলাদেশের সকল নাগরিকের প্রতি সদাচরনের নিমিত্ত একটি মুচলেকা সম্পাদন করিতে
                     বলা হইয়াছে, সেহেতু আমি এতদ্বারা অঙ্গীকার করিতেছি, উক্ত সময়ের জন্য (অথবা উক্ত
                     অনুসন্ধান সমাপ্ত না হওয়া পর্যন্ত) আমি বাংলাদেশ সরকার ও বাংলাদেশের সকল নাগরিকের প্রতি
                     সদাচরন করিব, এবং ইহা লঙ্ঘন করিলে আমি বাংলাদেশ সরকারকে _ _ _ _ _ _ _ _ _ টাকা দিতে বাধ্য থাকিব।
                </p>
                <br><br>
                <p style="text-align: right">
                    তারিখঃ'.$caseBanglaYear.' সালের '.$caseBanlaMonth .' মাসের '. $caseBanglaDate.' তারিখ। <br><br><br>

                        ............ <br>(স্বাক্ষর)
                </p>
            </div>';
        // dd($template);
        return $template;
    }


    //----------- শুনানি-----------------------------------------//
    public static function getHearingRequestShortOrderTemplate($appealInfo,$causeList){
        // dd($appealInfo);
        $law_section = CrpcSection::where('id',$appealInfo['appeal']->law_section)->select('crpc_id')->first()->crpc_id;
        $location= $appealInfo['appeal']->office_name;
        $upazila_name_bn= Upazila::where('id',$appealInfo['appeal']->upazila_id)->select('upazila_name_bn')->first()->upazila_name_bn;
        $case_date= $appealInfo['appeal']->case_date;
        $next_date= $appealInfo['appeal']->next_date;
        $case_no= $appealInfo['appeal']->case_no;
        $applicant=$appealInfo['applicantCitizen'];
        $defaulter=$appealInfo['defaulterCitizen'];
        $witness=$appealInfo['witnessCitizen'];
        $guarantorCitizen=$appealInfo['guarantorCitizen'];
        $case_details=  str_replace('&nbsp;', '', strip_tags($appealInfo['appeal']->case_details));
        $loanAmountBng=DataConversionService::toBangla($appealInfo['appeal']->loan_amount);

        $trialNextBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($next_date)));
        $trialBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($causeList->trial_date)));
        $trialBanglaDay=DataConversionService::toBangla(date('d',strtotime($causeList->trial_date)));
        $trialBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($causeList->trial_date)));
        $trialTime=date('h:i:s',strtotime($causeList->trial_time));
        $trialTimeAmPm=date('a',strtotime($causeList->trial_time));
        $time='';
        if($trialTimeAmPm == 'am'){
            $time='সকাল';
        }else{
            $time='বিকাল';
        }
        $trialBanglaYear=DataConversionService::toBangla(date("Y", strtotime($causeList->trial_date)));

        $caseBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($case_date)));
        $caseBanglaDay=DataConversionService::toBangla(date('d',strtotime($case_date)));
        $caseBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($case_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseBanglaYear=DataConversionService::toBangla(date("Y", strtotime($case_date)));
        $trialNextBanglaYear=DataConversionService::toBangla(date("Y", strtotime($next_date)));

        $caseNextBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($next_date)));
        $caseNextBanglaDay=DataConversionService::toBangla(date('d',strtotime($next_date)));
        $caseNextBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($next_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseNextBanglaYear=DataConversionService::toBangla(date("Y", strtotime($next_date)));

        $template='
            <style>
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                    padding: 10px;
                    font-weight: normal;
                }
            </style>
            <div style="text-align: center">
                <span style="font-size: medium;text-align: center;">এক্সিকিউটিভ ম্যাজিস্ট্রেট কোর্ট </span>
            </div>
            <div style="text-align: right">
                <span style="font-size: medium;">স্মারক নং.................. </span>
            </div>
            <div style="text-align: left">
                <span style="font-size: medium;">তারিখ '. $trialBanglaDate .'</span>
            </div>
                        <header>
                            <div style="text-align: center">
                                <h2>শুনানীর জন্য নোটিশ</h2> <br>
                            </div>
                        </header>
             <br>

             <br>
             <br>
             <div style="height:100%; padding: 10px;">
                <span style="float: left; font-size: medium">জিলা-'. user_district_name() .'   মোকাম '. globalUserInfo()->name .' এর<br>আদালত <br>মোকদ্দমা নং :'.$case_no.'    সন '.$trialBanglaYear.'   ইং
                </span>
                <br><br><br><br><br>
                <span style="float: left;">
                    নাম:- '.$defaulter[0]->citizen_name .', ঠিকানাঃ- '.$defaulter[0]->present_address.' সমীপে-
                </span>
                <br><br><br><br><br>
                
                 <span style="float: left;">যেহেতু সন্তোষজনক তদন্তের পর আমার নিকট প্রতীয়মান করা হইয়াছে,
                                                 '.$case_details.' 
                                            এবং আপনার অপরাধ সংঘটন করিবার সম্ভাবনা রহিয়াছে,সেহেতু এতদ্বারা '.$caseNextBanglaYear.' সালের '.$caseNextBanlaMonth.' মাসের '.$caseNextBanglaDay.' তারিখে আপনার শুনানীর দিন ধার্য করা হইল এবং এতদ্বারা আমি আপনাকে নির্দেশ দিতেছি, আপনি শুনানীর ধার্যকৃত দিনে নির্ধারিত সময়ের মধ্যে উপস্থিত থাকিবেন।

                 </span>
                <br><br><br>
                <br>
                <p>তারিখঃ'.$trialBanglaYear.' সালের '.$trialBanlaMonth .' মাসের '. $trialBanglaDay.' তারিখ। <br><br><br>
                    </p>
               
                 <p style=" text-align : left; color: blueviolet;">
                        <img src="'.globalUserInfo()->signature.'" alt="signature" width="100" height="50">

                        <br>'.'<b>'. globalUserInfo()->name .'</b>'.'<br> '.   globalUserRoleInfo()->role_name .', '.user_district_name().'
                    </p>
            </div>';
        // dd($template);
        return $template;
    }


    //----------- গ্রেফতারী পরোয়ানা-----------------------------------------//
    public static function getArrestWarrentShortOrderTemplate($appealInfo,$causeList){
        // dd($appealInfo);
        $law_section = CrpcSection::where('id',$appealInfo['appeal']->law_section)->select('crpc_id')->first()->crpc_id;
        $location= $appealInfo['appeal']->office_name;
        $upazila_name_bn= Upazila::where('id',$appealInfo['appeal']->upazila_id)->select('upazila_name_bn')->first()->upazila_name_bn;
        $case_date= $appealInfo['appeal']->case_date;
        $applicant=$appealInfo['applicantCitizen'];
        $defaulter=$appealInfo['defaulterCitizen'];
        $witness=$appealInfo['witnessCitizen'];
        $guarantorCitizen=$appealInfo['guarantorCitizen'];
        $case_details=  str_replace('&nbsp;', '', strip_tags($appealInfo['appeal']->case_details));
        $loanAmountBng=DataConversionService::toBangla($appealInfo['appeal']->loan_amount);

        $trialBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($causeList->trial_date)));
        $trialBanglaDay=DataConversionService::toBangla(date('d',strtotime($causeList->trial_date)));
        $trialBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($causeList->trial_date)));
        $trialTime=date('h:i:s',strtotime($causeList->trial_time));
        $trialTimeAmPm=date('a',strtotime($causeList->trial_time));
        $time='';
        if($trialTimeAmPm == 'am'){
            $time='সকাল';
        }else{
            $time='বিকাল';
        }
        $trialBanglaYear=DataConversionService::toBangla(date("Y", strtotime($causeList->trial_date)));

        $caseBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($case_date)));
        $caseBanglaDay=DataConversionService::toBangla(date('d',strtotime($case_date)));
        $caseBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($case_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseBanglaYear=DataConversionService::toBangla(date("Y", strtotime($case_date)));

        $template='
                    <style>
                        table, th, td {
                            border: none;
                            border-collapse: collapse;
                            font-weight: normal;
                        }
                    </style>
            <div >
                <span style="font-size:  medium;">বাংলাদেশ ফরম নং ৩৯০৫। <br> হাইকোর্ট ফৌজদারী পরোয়ানা ফরম নং ১২।  </span>
                        <header>
                            <div style="text-align: center">
                                <h2>গ্রেফতারী ওয়ারেন্ট</h2> <br>------------------------------<br>
                    <h5><b>(ফৌজদারী কার্যবিধির '.en2bn($law_section).' ধারা)</b></h5>
                    ------------------------------<br>

                            </div>
                        </header>
             <br>

             <br>
             <br>
            <div style="height:100%">
                <table>
                    <tr width="100%">
                        <td style="text-align: left; padding-top: 10px;" width="30%" >
                            '.en2bn(1).': নাম:- '.$defaulter[0]->citizen_name .', ঠিকানাঃ- '.$defaulter[0]->present_address.' 
                        </td>
                        <td width="40%">
                            
                        </td>
                        <td style="text-align: left;"width="30%">
                            '.en2bn(1).': ভারপ্রাপ্ত কর্মকর্তা(ওসি) '.$upazila_name_bn.' পুলিশ স্টেশন <br> এর প্রতি
                        </td>
                    </tr>
                </table>
                <p> '.$defaulter[0]->present_address.' নিবাসী '.$case_details.' অপরাধে  অভিযুক্ত হইয়াছে, অতএব আপনার প্রতি এতদ্বারা আদেশ করা যাইতেছে যে আপনি '.$defaulter[0]->citizen_name .' ধরিয়া আমার নিকটে উপস্থিত করিবেন। আপনি ইহা অমান্য করিবেন না।
                </p>
                <br><br>
                <p style="text-align: right">
                    তারিখঃ'.$caseBanglaYear.' সালের '.$caseBanlaMonth .' মাসের '. $caseBanglaDate.' তারিখ। <br><br><br>


                </p>
                 <p style=" text-align : right; color: blueviolet;">
                        <img src="'.globalUserInfo()->signature.'" alt="signature" width="100" height="50">

                        <br>'.'<b>'. globalUserInfo()->name .'</b>'.'<br> '.   globalUserRoleInfo()->role_name .', '.user_district_name().'
                    </p>
            </div>';
        // dd($template);
        return $template;
    }


    //----------- শান্তিরক্ষার মুচলেকা-----------------------------------------//
    public static function getPeaceBondShortOrderTemplate($appealInfo,$causeList){
        // dd($appealInfo);
        $location= $appealInfo['appeal']->office_name;
        $case_date= $appealInfo['appeal']->case_date;
        $applicant=$appealInfo['applicantCitizen'];
        $defaulter=$appealInfo['defaulterCitizen'];
        $witness=$appealInfo['witnessCitizen'];
        $guarantorCitizen=$appealInfo['guarantorCitizen'];
        $case_details=  str_replace('&nbsp;', '', strip_tags($appealInfo['appeal']->case_details));
        $loanAmountBng=DataConversionService::toBangla($appealInfo['appeal']->loan_amount);

        $trialBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($causeList->trial_date)));
        $trialBanglaDay=DataConversionService::toBangla(date('d',strtotime($causeList->trial_date)));
        $trialBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($causeList->trial_date)));
        $trialTime=date('h:i:s',strtotime($causeList->trial_time));
        $trialTimeAmPm=date('a',strtotime($causeList->trial_time));
        $time='';
        if($trialTimeAmPm == 'am'){
            $time='সকাল';
        }else{
            $time='বিকাল';
        }
        $trialBanglaYear=DataConversionService::toBangla(date("Y", strtotime($causeList->trial_date)));

        $caseBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($case_date)));
        $caseBanglaDay=DataConversionService::toBangla(date('d',strtotime($case_date)));
        $caseBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($case_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseBanglaYear=DataConversionService::toBangla(date("Y", strtotime($case_date)));

        $template='
                    <style>
                        table, th, td {
                            border: 1px solid black;
                            border-collapse: collapse;
                            padding: 10px;
                            font-weight: normal;
                        }
                    </style>
            <div >
                <span style="font-size:  medium;">বাংলাদেশ ফরম নং ১০২৮  </span>
                        <header>
                            <div style="text-align: center">
                                <h3>শান্তি রক্ষার মুচলেকা</h3>

                            </div>
                        </header>
             <br>

             <br>
             <br>
            <div style="height:100%">

                <p>যেহেতু '.$applicant[0]->present_address .' এর অধিবাসী আমাকে '.$defaulter[0]->citizen_name.' '.en2bn($trialTime). '  সময়ের জন্য (অথবা আদালতে
                     এক্ষুণে '.$case_details.' বিষয় সম্পর্কে যে অনুসন্ধান চলিতেছে তাহা সমাপ্ত না হওয়া পর্যন্ত) শান্তিরক্ষার নিমিত্ত একটি মুচলেকা সম্পাদন করিতে বলা হইয়াছে, সেহেতু আমি এতদ্বারা অঙ্গীকার করিতেছি যে, উক্ত সময়ের মধ্যে (অথবা উক্ত তদন্ত সমাপ্ত না হওয়া পর্যন্ত) আমি কোন প্রকার শান্তিভঙ্গ করিব না, অথবা শান্তিভঙ্গ হইতে পারে এমন কাজ করিব না, এবং ইহা লঙ্ঘন করিলে আমি বাংলাদেশ সরকারকে………… টাকা দিতে বাধ্য থাকিব।
                </p>
                <br><br>
                <p style="text-align: right">
                    তারিখঃ'.$caseBanglaYear.' সালের '.$caseBanlaMonth .' মাসের '. $caseBanglaDate.' তারিখ। <br><br><br>

                        ............ <br>(স্বাক্ষর)
                </p>
            </div>';
        // dd($template);
        return $template;
    }

    //----------- কারণ দর্শানো নোটিশ-----------------------------------------//
    public static function getShowCauseShortOrderTemplate($appealInfo,$causeList){
        // dd($appealInfo);
        $law_section = CrpcSection::where('id',$appealInfo['appeal']->law_section)->select('crpc_id')->first()->crpc_id;
        $location= $appealInfo['appeal']->office_name;
        $upazila_name_bn= Upazila::where('id',$appealInfo['appeal']->upazila_id)->select('upazila_name_bn')->first()->upazila_name_bn;
        $case_date= $appealInfo['appeal']->case_date;
        $next_date= $appealInfo['appeal']->next_date;
        $case_no= $appealInfo['appeal']->case_no;
        $applicant=$appealInfo['applicantCitizen'];
        $defaulter=$appealInfo['defaulterCitizen'];
        $witness=$appealInfo['witnessCitizen'];
        $guarantorCitizen=$appealInfo['guarantorCitizen'];
        $case_details=  str_replace('&nbsp;', '', strip_tags($appealInfo['appeal']->case_details));
        $loanAmountBng=DataConversionService::toBangla($appealInfo['appeal']->loan_amount);

        $trialNextBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($next_date)));
        $trialBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($causeList->trial_date)));
        $trialBanglaDay=DataConversionService::toBangla(date('d',strtotime($causeList->trial_date)));
        $trialBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($causeList->trial_date)));
        $trialTime=date('h:i:s',strtotime($causeList->trial_time));
        $trialTimeAmPm=date('a',strtotime($causeList->trial_time));
        $time='';
        if($trialTimeAmPm == 'am'){
            $time='সকাল';
        }else{
            $time='বিকাল';
        }
        $trialBanglaYear=DataConversionService::toBangla(date("Y", strtotime($causeList->trial_date)));

        $caseBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($case_date)));
        $caseBanglaDay=DataConversionService::toBangla(date('d',strtotime($case_date)));
        $caseBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($case_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseBanglaYear=DataConversionService::toBangla(date("Y", strtotime($case_date)));
        $trialNextBanglaYear=DataConversionService::toBangla(date("Y", strtotime($next_date)));

        $caseNextBanglaDate=DataConversionService::toBangla(date('d-m-Y',strtotime($next_date)));
        $caseNextBanglaDay=DataConversionService::toBangla(date('d',strtotime($next_date)));
        $caseNextBanlaMonth=DataConversionService::getBanglaMonth((int) date('m',strtotime($next_date)));
        // $caseTime=date('h:i:s a',strtotime($causeList->case_time));
        $caseNextBanglaYear=DataConversionService::toBangla(date("Y", strtotime($next_date)));

        $template='
            <style>
                        table, th, td {
                            border: 1px solid black;
                            border-collapse: collapse;
                            padding: 10px;
                            font-weight: normal;
                        }
                    </style>
            <div style="text-align: center">
                <span style="font-size: medium;text-align: center;">এক্সিকিউটিভ ম্যাজিস্ট্রেট কোর্ট<br>বাংলাদেশ ফর্ম নং-৩৩৮ </span>
            </div>
            <div style="text-align: right">
                <span style="font-size: medium;">স্মারক নং.................. </span>
            </div>
            <div style="text-align: left">
                <span style="font-size: medium;">তারিখ '. $trialBanglaDate .'</span>
            </div>
                        <header>
                            <div style="text-align: center">
                                <h2>কারন দর্শাইবার নোটিশ</h2> <br>
                            </div>
                        </header>
             <br>

             <br>
             <br>
             <div style="height:100%; padding: 10px;">
                <span style="float: left; font-size: medium">জিলা-'. user_district_name() .'   মোকাম '. globalUserInfo()->name .' এর<br>আদালত <br>মোকদ্দমা নং :'.$case_no.'    সন '.$trialBanglaYear.'   ইং
                </span>
                <br><br><br><br><br>
                <span style="float: center;">
                      নাম:- '.$defaulter[0]->citizen_name .', ঠিকানাঃ- '.$defaulter[0]->present_address.' ।
                </span>
                <br><br><br><br><br>
                
                 <span style="float: left;">যেহেতু '.$applicant[0]->citizen_name .' অত্র আদালতে দরখাস্ত করিয়াছেন যে, 
                                            '. $case_details .'
                                            অতএব আপনাকে এতদ্বারা সাবধান করিয়া দেওয়া যাইতেছে যে,আপনি স্বয়ং বা রীতিমত উপদেশ প্রাপ্ত '.$caseNextBanglaDate.'  তারিখ পূর্বাহ্নে বেলা ১০ ঘটিকার  সময় এই আদালতে উপস্থিত হইয়া উক্ত দরখাস্তের বিরুদ্ধে কারন  দর্শাইবেন নতুবা উক্ত দরখাস্তের একতরফা শুনাণী ও বিচার হইবে।


                 </span>
                <br><br><br>
                <br>
                <p>ইতি-অদ্য সন '.$trialBanglaYear.'  সালের '.$trialBanlaMonth .' মাসের '. $trialBanglaDay.' তারিখে 

                    </p>
               
                 <p style=" text-align : left; color: blueviolet;">
                        <img src="'.globalUserInfo()->signature.'" alt="signature" width="100" height="50">

                        <br>'.'<b>'. globalUserInfo()->name .'</b>'.'<br> '.   globalUserRoleInfo()->role_name .', '.user_district_name().'
                    </p>
            </div>';
        // dd($template);
        return $template;
    }


    


}
