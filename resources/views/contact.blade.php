<?php
//ini_set('display_errors', 1);
$webroot = getWebRoot();

?>
<!DOCTYPE html>
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/ico"
          href="<?PHP echo $webroot . "/" . \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() . "/favicon.ico"; ?>"/>
    <link rel="shortcut icon" type="image/ico"
          href="<?PHP echo $webroot . "/" . \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() . "/favicon.ico"; ?>"/>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <link href="{{$webroot}}css/bootstrap.min.css" rel="stylesheet">-->
    <!--    <link href="css/bootstrap-theme.min.css" rel="stylesheet">-->
    <!-- <link href="{{$webroot}}css/animate.css" rel="stylesheet">-->

    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/default.css?v=1.1"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/compiled/app.min.css?v=1.4"/>
    <link rel="stylesheet" media="screen" type="text/css"
          href="<?php echo $webroot; ?>css/company.php"/>

    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/font-awesome/css/all.css">

    <!--<script type="text/javascript" src="<?php echo $webroot; ?>js/jquery_2.1.3_jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
-->
    <link rel="stylesheet" href="{{$webroot}}css/jquery-ui.min.css"/>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/iscroll.min.js"></script>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/compiled/built.min.js"></script>
    @if(!env('APP_DEBUG') && env('APP_ENV') == 'production')
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-127417577-1"></script>
        <script>window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push(arguments);
			}

			gtag('js', new Date());
			gtag('config', 'UA-127417577-1');</script>
    @endif


    <title><?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?></title>
</head>

<body>

<header class="top_sec value_span1">
    <div class="container">
        <div class="row_wrap">
            <div class="nav_wrap">
                <nav class="navbar navbar-expand-lg bg-body-tertiary">
                    <a class="navbar-brand" href="{{$webroot}}">
                        <img src="{{ $webroot.\LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() .  "/logo.png"}}"
                             alt="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?>"
                             title="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?>"/>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="{{$webroot}}#home">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{$webroot}}#our_benefits">Our Benefits</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{$webroot}}#faq">FAQ</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{$webroot}}#contact" class="nav-link">Contact</a>
                            </li>
                        </ul>
                        <div class="buttons_wrap">
                            <a class="button blue" href="{{$webroot}}login">Sign In</a>
                            <a class="button transparent" href="{{$webroot}}contact-us">Contact us</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>

<div class = "white_box_outer contact_us">
    <div class="container">
        <div class ="white_box value_span8">
            <div class = "com_acc">
                @if(session()->has('success'))
                    <div class="heading_holder success">
                        <h3>Thanks for contacting CPA Admin!</h3>
                        <p>We will review your application and contact you soon to get you set up.</p>
                    </div>
                @else
                    <form method="POST" action="{{route('contact.send')}}" id="contact_us_form">
                        <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                        <div class = "heading_holder">
                            <h3>Contact Us</h3>
                            <p>Submit the form below to let us know about your group, experience, type of traffic and current sales volume.</p>
                        </div>
                        <div class="mb-3">
                            <div class="row p-0">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input id="first_name" class="form-control" type="text" name="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
                                    @if ($errors->has('first_name'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('first_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input class="form-control" id="last_name" type="text" name ="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
                                    @if ($errors->has('last_name'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('last_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row p-0">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input class="form-control" id="office_name" type="text" name ="office_name" placeholder="Group/Office Name" value="{{ old('office_name') }}" required>
                                    @if ($errors->has('office_name'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('office_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input class="form-control" id="email" type="text" name="email" placeholder="E-mail" value="{{ old('email') }}" required>
                                    @if ($errors->has('email'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row p-0">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <select name="messenger" id="messenger" class="form-control form-select" required>
                                        <option value="">Select Instant Messenger</option>
                                        <option value="skype" @if(old('messenger') == 'skype') selected @endif>Skype</option>
                                        <option value="telegram" @if(old('messenger') == 'telegram') selected @endif>Telegram</option>
                                    </select>
                                    @if ($errors->has('messenger'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('messenger') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input class="form-control" id="messenger_name" type="text" name="messenger_name" placeholder="Messenger Name" value="{{ old('messenger_name') }}" required>
                                    @if ($errors->has('messenger_name'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('messenger_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input class="form-control" id="location" type="text" name="location" placeholder="Location" value="{{ old('location') }}" required>
                            @if ($errors->has('location'))
                                <span class="errors">
                                    <strong>{{ $errors->first('location') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <select class="form-control form-select" name="account_type" id="account_type" required>
                                <option value="">Which Best Describes you?</option>
                                <option value="Network Owner" @if(old('account_type') == 'Network Owner') selected @endif>Network Owner</option>
                                <option value="Office Owner" @if(old('account_type') == 'Office Owner') selected @endif>Office Owner</option>
                                <option value="Office Manager" @if(old('account_type') == 'Office Manager') selected @endif>Office Manager</option>
                                <option value="Office Admin" @if(old('account_type') == 'Office Admin') selected @endif>Office Admin</option>
                                <option value="Recruiter" @if(old('account_type') == 'Recruiter') selected @endif>Recruiter</option>
                            </select>
                            @if ($errors->has('account_type'))
                                <span class="errors">
                                    <strong>{{ $errors->first('account_type') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <select class="form-control form-select" name="agents" id="agents" required>
                                <option value="">Number of Agents?</option>
                                <option value="1-5" @if(old('agents') == '1-5') selected @endif>1-5</option>
                                <option value="6-10" @if(old('agents') == '6-10') selected @endif>6-10</option>
                                <option value="11-20" @if(old('agents') == '11-20') selected @endif>11-20</option>
                                <option value="21-30" @if(old('agents') == '21-30') selected @endif>21-30</option>
                                <option value="31-50" @if(old('agents') == '31-50') selected @endif>31-50</option>
                                <option value="50-100" @if(old('agents') == '50-100') selected @endif>50-100</option>
                                <option value="101+" @if(old('agents') == '101+') selected @endif>101+</option>
                            </select>
                            @if ($errors->has('agents'))
                                <span class="errors">
                                    <strong>{{ $errors->first('agents') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <fieldset>
                                <legend>Seeking offer types</legend>
                                <p>(choose all that apply)</p>
                               <div class="checks_wrap">
                                   <div class="form-check">
                                       <input name="offer_types[]" class="form-check-input" type="checkbox" value="Dating" id="dating"
                                              @if( is_array(old('offer_types')) && in_array("Dating", old('offer_types'))) checked @endif
                                       >
                                       <label class="form-check-label" for="dating">
                                           Dating
                                       </label>
                                   </div>
                                   <div class="form-check">
                                       <input name="offer_types[]" class="form-check-input" type="checkbox" value="Cams" id="cams"
                                              @if( is_array(old('offer_types')) && in_array("Cams", old('offer_types'))) checked @endif
                                       >
                                       <label class="form-check-label" for="cams">
                                           Cams
                                       </label>
                                   </div>
                                   <div class="form-check">
                                       <input name="offer_types[]"  class="form-check-input" type="checkbox" value="Nutra" id="nutra"
                                              @if( is_array(old('offer_types')) && in_array("Nutra", old('offer_types'))) checked @endif
                                       >
                                       <label class="form-check-label" for="nutra">
                                           Nutra
                                       </label>
                                   </div>
                                   <div class="form-check">
                                       <input name="offer_types[]" class="form-check-input" type="checkbox" value="Mens Health" id="mens_health"
                                              @if( is_array(old('offer_types')) && in_array("Mens Health", old('offer_types'))) checked @endif
                                       >
                                       <label class="form-check-label" for="mens_health">
                                           Mens Health
                                       </label>
                                   </div>
                               </div>
                            </fieldset>
                            @if ($errors->has('offer_types'))
                                <span class="errors">
                                    <strong>{{ $errors->first('offer_types') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <select class="form-control form-select" name="experience" id="experience" required>
                                <option value="">Years Experience</option>
                                <option value="0-1" @if(old('experience') == '0-1') selected @endif >0-1</option>
                                <option value="1-3" @if(old('experience') == '1-3') selected @endif >1-3</option>
                                <option value="3-5" @if(old('experience') == '3-5') selected @endif >3-5</option>
                                <option value="5+" @if(old('experience') == '5+') selected @endif >5+</option>
                            </select>
                            @if ($errors->has('experience'))
                                <span class="errors">
                                    <strong>{{ $errors->first('experience') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <select class="form-control form-select" name="sales" id="sales" required>
                                <option value="">Current Sales Per Week</option>
                                <option value="0-9" @if(old('sales') == '0-9') selected @endif >0-9</option>
                                <option value="10-24" @if(old('sales') == '10-24') selected @endif >10-24</option>
                                <option value="25-49" @if(old('sales') == '25-49') selected @endif >25-49</option>
                                <option value="50-99" @if(old('sales') == '50-99') selected @endif >50-99</option>
                                <option value="100-149" @if(old('sales') == '100-149') selected @endif >100-149</option>
                                <option value="150-249" @if(old('sales') == '150-249') selected @endif >150-249</option>
                                <option value="250+" @if(old('sales') == '250+') selected @endif >250+</option>
                            </select>
                            @if ($errors->has('sales'))
                                <span class="errors">
                                    <strong>{{ $errors->first('sales') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" name="additional_info" id="additional_info" rows="10" placeholder="Additional Info" required>{{ old('additional_info') }}</textarea>
                            @if ($errors->has('additional_info'))
                                <span class="errors">
                                    <strong>{{ $errors->first('additional_info') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <input type="submit"
                                   name="button"
                                   class="button blue"
                                   value="Submit"
                            />
                        </div>
                    </form>
                @endif
            </div>
        </div><!-- white_box -->
    </div>
</div><!-- white_box_outer -->
@include('layouts.contact-footer')

@yield('footer')

</body>
</html>

