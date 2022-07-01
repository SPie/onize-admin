@section('title', 'Profile')

<div>
    <div class="">
        <h2>{{ __('title.profile') }}</h2>

        <div x-data="{ edit: @entangle('editMode') }">
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
    </div>
</div>
