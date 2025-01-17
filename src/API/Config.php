<?php
namespace Jalno\Userpanel\API;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;
use Jalno\Config\Models\Config as ConfigModel;
use Jalno\Userpanel\Rules\ConfigValidatorRule;
use Jalno\Userpanel\Contracts\IConfigValidatorContainer;

class Config extends API
{

    protected Container $app;
    protected IConfigValidatorContainer $validatorContainer;

    public function __construct(Container $app, IConfigValidatorContainer $validatorContainer)
    {
        $this->app = $app;
        $this->validatorContainer = $validatorContainer;
    }

    /**
     * @return array<string,mixed>
     */
    public function search(array $parameters): array
    {
        $this->requireAbility("userpanel_configs_search");

        $configs = $this->validatorContainer->all();
        if (empty($parameters) or !isset($parameters["configs"])) {
            $parameters["configs"] = $configs;
        } else {
            $parameters = Validator::validate($parameters, array(
                'configs' => ['required', 'array', 'min:1'],
                'configs.*' => ['string', Rule::in($configs)],
            ));
        }

        $configs = [];
        foreach ($parameters["configs"] as $name) {
            $configs[$name] = config($name);
        }

        return $configs;
    }

    /**
     * @param array<string,mixed> $parameters
     */
    public function update(array $parameters): void
    {
        $this->requireAbility("userpanel_configs_update");

        $rule = new ConfigValidatorRule($this->validatorContainer);

        $parameters = Validator::validate($parameters, array(
            "config" => ["required", "array", "min:1"],
            "config.*" => [$rule],
        ));

        $config = $this->app->make("config");
        foreach ($parameters["config"] as $item) {
            $name = $item["name"];
            $value = $item["value"];

            $namespace = implode(".", array_slice(explode(".", $name, 3), 0, 2));

            $config->set($name, $value);

            $model = ConfigModel::query();
            $model->where("name", "like", "{$namespace}%");
            $model->delete();

            $model = new ConfigModel();
            $model->fill(['name' => $namespace, 'value' => $config->get($namespace)]);
            $model->save();
        }
    }
}
