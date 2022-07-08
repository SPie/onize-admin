@section('title', 'Profile')

<div>
    <div class="">
        <h2>{{ __('title.profile') }}</h2>

        <div x-data="{ edit: @entangle('editEmail') }">
            <form wire:submit.prevent="editEmail" x-show="edit">
                <div>
                    <div>
                        <label for="email">{{ __('label.email') }}</label>
                        <div>
                            <input wire:model="email" type="email" id="email" required="required" />
                        </div>
                    </div>

                    <div>
                        <button type="button" x-on:click="edit = false">{{ __('button.cancel') }}</button>
                    </div>
                    <div>
                        <button type="submit">{{ __('button.save') }}</button>
                    </div>
                </div>
            </form>
            <div x-show="!edit">
                <div>
                    {{ __('label.email') }}
                </div>
                <div>
                    {{ $this->email }}
                </div>
                <div>
                    <button type="button" x-on:click="edit = true">{{ __('button.edit') }}</button>
                </div>
            </div>
        </div>
        <div x-data="{ edit: @entangle('editPassword') }">
            <form wire:submit.prevent="editPassword" x-show="edit">
                <div>
                    <div>
                        <label for="currentPassword">{{ __('label.currentPassword') }}</label>
                        <div>
                            <input wire:model="currentPassword" type="password" id="password" required="required" />
                        </div>
                    </div>
                    <div>
                        <label for="newPassword">{{ __('label.newPassword') }}</label>
                        <div>
                            <input wire:model="newPassword" type="password" id="newPassword" required="required" />
                        </div>
                    </div>
                    <div>
                        <label for="passwordConfirm">{{ __('label.passwordConfirm') }}</label>
                        <div>
                            <input wire:model="passwordConfirm" type="password" id="passwordConfirm" required="required" />
                        </div>
                    </div>

                    <div>
                        <button type="button" x-on:click="edit = false">{{ __('button.cancel') }}</button>
                    </div>
                    <div>
                        <button type="submit">{{ __('button.save') }}</button>
                    </div>
                </div>
            </form>
            <div x-show="!edit">
                <div>
                    {{ __('label.password') }}
                </div>
                <div>
                    *********
                </div>
                <div>
                    <button type="button" x-on:click="edit = true">{{ __('button.edit') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
