import Impersonation from './Impersonation'
import Token from './Token'
import Authentication from './Authentication'
import Role from './Role'
import User from './User'
import Invitation from './Invitation'

const Auth = {
    Impersonation: Object.assign(Impersonation, Impersonation),
    Token: Object.assign(Token, Token),
    Authentication: Object.assign(Authentication, Authentication),
    Role: Object.assign(Role, Role),
    User: Object.assign(User, User),
    Invitation: Object.assign(Invitation, Invitation),
}

export default Auth