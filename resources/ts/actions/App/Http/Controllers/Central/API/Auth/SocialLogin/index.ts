import GoogleSocialLoginController from './GoogleSocialLoginController'
import MicrosoftSocialLoginController from './MicrosoftSocialLoginController'

const SocialLogin = {
    GoogleSocialLoginController: Object.assign(GoogleSocialLoginController, GoogleSocialLoginController),
    MicrosoftSocialLoginController: Object.assign(MicrosoftSocialLoginController, MicrosoftSocialLoginController),
}

export default SocialLogin