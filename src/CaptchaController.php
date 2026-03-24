<?php

namespace Jason\Captcha;

use Illuminate\Routing\Controller;

class CaptchaController extends Controller
{
    /**
     * Get captcha image.
     */
    public function getCaptcha(Captcha $captcha, string $style = 'default'): mixed
    {
        if (ob_get_contents()) {
            ob_clean();
        }

        return $captcha->create($style);
    }

    /**
     * Get captcha API response.
     */
    public function getCaptchaApi(Captcha $captcha, string $style = 'default'): mixed
    {
        return $captcha->create($style, true);
    }
}
