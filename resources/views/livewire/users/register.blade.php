@section('title', 'Register')

<div>
    <div class="">
        <h2>{{ __('title.register') }}</h2>

        <div>
            <form wire:submit.prevent="register">
                <div>
                    <div>
                        <label for="email">{{ __('label.email') }}</label>
                        <div>
                            <input wire:model="email" type="email" id="email" required="required" />
                        </div>
                    </div>
                    <div>
                        <label for="password">{{ __('label.password') }}</label>
                        <div>
                            <input wire:model="password" type="password" id="password" required="required" />
                        </div>
                    </div>
                    <div>
                        <label for="password">{{ __('label.passwordConfirm') }}</label>
                        <div>
                            <input wire:model="passwordConfirm" type="password" id="passwordConfirm" required="required" />
                        </div>
                    </div>

                    <div>
                        <button type="submit">{{ __('button.register') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
