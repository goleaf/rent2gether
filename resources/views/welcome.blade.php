<x-layouts.app title="Overview">
    <div class="space-y-5 sm:space-y-6 lg:space-y-8">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-3">
                <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0">
                    <flux:badge color="teal" icon="sparkles">Marketplace</flux:badge>
                    <flux:badge color="zinc" icon="calendar">Bookings</flux:badge>
                    <flux:badge color="sky" icon="check-circle">Verification</flux:badge>
                </div>

                <div>
                    <flux:heading size="xl" level="1">Workspace overview</flux:heading>
                    <flux:text class="mt-2 max-w-2xl text-base text-zinc-600 dark:text-zinc-400">
                        Track spaces, bookings, member checks, and marketplace readiness from one focused interface.
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-3">
                <flux:button
                    x-data
                    x-on:click="$flux.toast({ heading: 'Workspace checked', text: 'Spaces, bookings, and member queues are ready for review.', variant: 'success' })"
                    icon="check-circle"
                    variant="primary"
                    class="w-full sm:w-auto"
                >
                    Run check
                </flux:button>

                <flux:modal.trigger name="new-space">
                    <flux:button icon="plus" class="w-full sm:w-auto">New space</flux:button>
                </flux:modal.trigger>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-3 min-[380px]:grid-cols-2 sm:gap-4 xl:grid-cols-4">
            <flux:card size="sm" class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Active spaces</flux:text>
                        <flux:heading size="lg" class="mt-1">24</flux:heading>
                    </div>
                    <flux:badge color="green" icon="arrow-trending-up">12%</flux:badge>
                </div>
                <flux:progress value="72" color="teal" />
            </flux:card>

            <flux:card size="sm" class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Pending bookings</flux:text>
                        <flux:heading size="lg" class="mt-1">18</flux:heading>
                    </div>
                    <flux:badge color="amber" icon="clock">Review</flux:badge>
                </div>
                <flux:progress value="48" color="amber" />
            </flux:card>

            <flux:card size="sm" class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Verified members</flux:text>
                        <flux:heading size="lg" class="mt-1">312</flux:heading>
                    </div>
                    <flux:badge color="blue" icon="users">Live</flux:badge>
                </div>
                <flux:progress value="86" color="blue" />
            </flux:card>

            <flux:card size="sm" class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">System baseline</flux:text>
                        <flux:heading size="lg" class="mt-1">100%</flux:heading>
                    </div>
                    <flux:badge color="teal" icon="check-circle">Ready</flux:badge>
                </div>
                <flux:progress value="100" color="teal" />
            </flux:card>
        </section>

        <flux:callout color="teal" icon="sparkles">
            <flux:callout.heading>Daily operations are on track</flux:callout.heading>
            <flux:callout.text>
                Hosts, guests, spaces, and booking requests can move through one focused workspace.
            </flux:callout.text>
        </flux:callout>

        <section class="grid gap-5 lg:gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <flux:card class="space-y-5 !p-4 sm:!p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading>Booking pipeline</flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            Current marketplace activity by operational status.
                        </flux:text>
                    </div>

                    <flux:button.group class="w-full sm:w-auto">
                        <flux:button size="sm" variant="filled">Today</flux:button>
                        <flux:button size="sm">Week</flux:button>
                        <flux:button size="sm">Month</flux:button>
                    </flux:button.group>
                </div>

                <flux:tab.group>
                    <flux:tabs variant="segmented" scrollable scrollable:fade>
                        <flux:tab name="bookings" icon="calendar" selected>Bookings</flux:tab>
                        <flux:tab name="spaces" icon="building-office-2">Spaces</flux:tab>
                        <flux:tab name="members" icon="users">Members</flux:tab>
                    </flux:tabs>

                    <flux:tab.panel name="bookings" selected class="pt-6">
                        <div class="space-y-3 md:hidden">
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-white/10">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">R2G-1048</flux:text>
                                        <flux:heading size="sm" class="mt-1">Old Town studio</flux:heading>
                                    </div>
                                    <flux:badge color="amber">Pending</flux:badge>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Value</span>
                                    <span class="font-medium">EUR 420</span>
                                </div>
                            </div>

                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-white/10">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">R2G-1047</flux:text>
                                        <flux:heading size="sm" class="mt-1">Riverside loft</flux:heading>
                                    </div>
                                    <flux:badge color="green">Confirmed</flux:badge>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Value</span>
                                    <span class="font-medium">EUR 760</span>
                                </div>
                            </div>

                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-white/10">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">R2G-1046</flux:text>
                                        <flux:heading size="sm" class="mt-1">North hub desk</flux:heading>
                                    </div>
                                    <flux:badge color="blue">Screening</flux:badge>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Value</span>
                                    <span class="font-medium">EUR 190</span>
                                </div>
                            </div>
                        </div>

                        <div class="hidden md:block">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Request</flux:table.column>
                                    <flux:table.column>Space</flux:table.column>
                                    <flux:table.column>Status</flux:table.column>
                                    <flux:table.column align="end">Value</flux:table.column>
                                </flux:table.columns>

                                <flux:table.rows>
                                    <flux:table.row>
                                        <flux:table.cell variant="strong">R2G-1048</flux:table.cell>
                                        <flux:table.cell>Old Town studio</flux:table.cell>
                                        <flux:table.cell><flux:badge color="amber">Pending</flux:badge></flux:table.cell>
                                        <flux:table.cell align="end" variant="strong">EUR 420</flux:table.cell>
                                    </flux:table.row>

                                    <flux:table.row>
                                        <flux:table.cell variant="strong">R2G-1047</flux:table.cell>
                                        <flux:table.cell>Riverside loft</flux:table.cell>
                                        <flux:table.cell><flux:badge color="green">Confirmed</flux:badge></flux:table.cell>
                                        <flux:table.cell align="end" variant="strong">EUR 760</flux:table.cell>
                                    </flux:table.row>

                                    <flux:table.row>
                                        <flux:table.cell variant="strong">R2G-1046</flux:table.cell>
                                        <flux:table.cell>North hub desk</flux:table.cell>
                                        <flux:table.cell><flux:badge color="blue">Screening</flux:badge></flux:table.cell>
                                        <flux:table.cell align="end" variant="strong">EUR 190</flux:table.cell>
                                    </flux:table.row>
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    </flux:tab.panel>

                    <flux:tab.panel name="spaces" class="pt-6">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-white/10">
                                <flux:heading size="sm">Apartments</flux:heading>
                                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">12 active listings</flux:text>
                            </div>
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-white/10">
                                <flux:heading size="sm">Studios</flux:heading>
                                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">8 active listings</flux:text>
                            </div>
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-white/10">
                                <flux:heading size="sm">Workspaces</flux:heading>
                                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">4 active listings</flux:text>
                            </div>
                        </div>
                    </flux:tab.panel>

                    <flux:tab.panel name="members" class="pt-6">
                        <flux:accordion>
                            <flux:accordion.item heading="Identity checks" expanded>
                                Member profiles are ready for policy-backed verification workflows.
                            </flux:accordion.item>
                            <flux:accordion.item heading="Host onboarding">
                                Hosts can be connected to listings, documents, and availability windows.
                            </flux:accordion.item>
                        </flux:accordion>
                    </flux:tab.panel>
                </flux:tab.group>
            </flux:card>

            <div class="space-y-5 lg:space-y-6">
                <flux:card class="space-y-5 !p-4 sm:!p-6">
                    <div>
                        <flux:heading>Quick filters</flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            Narrow the workspace by date and property type.
                        </flux:text>
                    </div>

                    <div class="grid gap-4">
                        <flux:field>
                            <flux:label>Availability window</flux:label>
                            <flux:date-picker mode="range" with-presets />
                        </flux:field>

                        <flux:field>
                            <flux:label>Property type</flux:label>
                            <flux:select variant="combobox" placeholder="Choose type">
                                <flux:select.option>Apartment</flux:select.option>
                                <flux:select.option>Studio</flux:select.option>
                                <flux:select.option>Workspace</flux:select.option>
                            </flux:select>
                        </flux:field>
                    </div>
                </flux:card>

                <flux:card class="space-y-5 !p-4 sm:!p-6">
                    <div>
                        <flux:heading>Launch path</flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            Foundation work for the next product pass.
                        </flux:text>
                    </div>

                    <flux:timeline size="lg">
                        <flux:timeline.item status="complete">
                            <flux:timeline.indicator>
                                <flux:icon.check variant="micro" />
                            </flux:timeline.indicator>
                            <flux:timeline.content>
                                <flux:heading size="sm">Workspace foundation</flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">Core navigation and status surfaces are in place.</flux:text>
                            </flux:timeline.content>
                        </flux:timeline.item>

                        <flux:timeline.item status="complete">
                            <flux:timeline.indicator>
                                <flux:icon.check variant="micro" />
                            </flux:timeline.indicator>
                            <flux:timeline.content>
                                <flux:heading size="sm">Operations dashboard</flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">Bookings, spaces, members, and checks share one view.</flux:text>
                            </flux:timeline.content>
                        </flux:timeline.item>

                        <flux:timeline.item status="current">
                            <flux:timeline.indicator>3</flux:timeline.indicator>
                            <flux:timeline.content>
                                <flux:heading size="sm">Listing workflows</flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">Availability, pricing, and approvals come next.</flux:text>
                            </flux:timeline.content>
                        </flux:timeline.item>
                    </flux:timeline>
                </flux:card>
            </div>
        </section>
    </div>

    <flux:modal name="new-space" class="space-y-6">
        <div>
            <flux:heading size="lg">New space</flux:heading>
            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                Create a draft listing for review before it goes live.
            </flux:text>
        </div>

        <div class="grid gap-4">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input placeholder="Riverside loft" />
            </flux:field>

            <flux:field>
                <flux:label>Type</flux:label>
                <flux:select variant="listbox" placeholder="Select type">
                    <flux:select.option>Apartment</flux:select.option>
                    <flux:select.option>Studio</flux:select.option>
                    <flux:select.option>Workspace</flux:select.option>
                </flux:select>
            </flux:field>
        </div>

        <div class="grid gap-2 sm:flex sm:justify-end sm:gap-3">
            <flux:modal.close>
                <flux:button variant="ghost" class="w-full sm:w-auto">Cancel</flux:button>
            </flux:modal.close>

            <flux:button
                x-data
                x-on:click="$flux.toast({ heading: 'Draft saved', text: 'The listing draft is ready for review.', variant: 'success' })"
                variant="primary"
                class="w-full sm:w-auto"
            >
                Save draft
            </flux:button>
        </div>
    </flux:modal>
</x-layouts.app>
