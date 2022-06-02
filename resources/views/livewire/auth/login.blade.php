@section('title', 'Sign In')

<div>
    <div class="">
        <h2>{{ __('title.sign-in') }}</h2>

        <div>
            <form wire:submit.prevent="login">
                <div>
                    <div>
                        <label for="email">{{ __('label.email') }}</label>
                        <div>
                            <input wire:model="email" type="text" id="email" required="required" />
                        </div>
                    </div>
                    <div>
                        <label for="password">{{ __('label.password') }}</label>
                        <div>
                            <input wire:model="password" type="password" id="password" required="required" />
                        </div>
                    </div>

                    <div>
                        <button type="submit">{{ __('button.sign-in') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
