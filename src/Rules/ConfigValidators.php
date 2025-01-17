<?php
namespace Jalno\Userpanel\Rules;

use Jalno\Userpanel\Models\User;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Auth;
use Jalno\Userpanel\Contracts\IConfigValidatorContainer;

class ConfigValidators
{
    protected Container $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function addValidators()
    {
        $validator = $this->app->make(IConfigValidatorContainer::class);

        $validator->add("jalno.userpanel.register.enable", fn($value) => in_array($value, ["1", "0"], true));

        $validator->add("jalno.userpanel.register.usertype", function($value) {
            $user = Auth::user();

            return $user and in_array($value, $user->childrenTypes());
        });

        $validator->add("jalno.userpanel.register.status", fn($value) => in_array(User::ACTIVE, User::DEACTIVE, User::SUSPEND));
    }
}
