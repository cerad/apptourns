Added CeradAccountBundle to AppKernel

Had to add master password to parameters.ini

Had to load fosuserbundle under vendors

doctrine:schema:update ran as expected.

Step1
    fedId
    email - still need to deal with unique emails
    name
    password

Everything worked until tried to get userManager.

Need to setup security.

It was actually config.yml which has some cerad_account parameters.
