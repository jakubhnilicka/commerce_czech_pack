# About
This module provides a commerce checkout pane for agreement to personal data processing.

Pane is displayed only for registered users. User agreement is stored into user profile.
Anonymous users have not profile for storing agreement.

# Config
Go to /admin/commerce/config/checkout-flows to your flow.
There is "Agree to the personal data processing" pane.

Choose:
 - Prefix text
 - Link text
 - Link to content
 - Machine name of user profile agreement field
   - Field in the user profile for storing agreement. 
   - The field must be of checkbox type.  
 - Machine name of user profile agreement log field
   - Field in the user profile for storing time and IP of the agreement.
   - The field must be of long text (textarea) type.
 - Revoke agreement instruction
   - User with stored active agreement does not have to agree again.
   - When user has already confirmed agreement, this text is displayed instead of agreement checkbox.
   
Place "Agree to the personal data processing" pane into Review section.
