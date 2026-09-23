<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestão de Tráfego') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <!-- TABS -->
                <div class="border-b mb-4 flex gap-4">
                    <button
                        class="tab-btn border-b-2 border-blue-500 text-blue-600 pb-2"
                        data-tab="vesselfinder"
                    >
                        VesselFinder
                    </button>

                    <button
                        class="tab-btn text-gray-500 pb-2"
                        data-tab="marine"
                    >
                        MarineTraffic
                    </button>
                </div>

                <!-- CONTEÚDO -->
                <div>

                    <!-- VESSELFINDER (ATIVO) -->
                    <div id="vesselfinder" class="tab-content">
                       <script type="text/javascript">
                        // Map appearance
                        var width="100%";         // width in pixels or percentage
                        var height="1100";         // height in pixels
                        var latitude="0.00";      // center latitude (decimal degrees)
                        var longitude="0.00";     // center longitude (decimal degrees)
                        var zoom="3";             // initial zoom (between 3 and 18)
                        var names=false;          // always show ship names (defaults to false)

                    </script>
                    <script type="text/javascript" src="https://www.vesselfinder.com/aismap.js"></script>
                    </div>

                    <!-- MARINETRAFFIC -->
                    <div id="marine" class="tab-content hidden">
                        <iframe 
                    src="https://www.marinetraffic.com/en/ais/embed/zoom:3/centery:36/centerx:23/maptype:4/shownames:false/mmsi:0/shipid:0/fleet:/fleet_id:/vtypes:/showmenu:/remember:false"
                    class="w-full h-[1100px] border-0"
                    loading="lazy"
                ></iframe>
                    </div>

                </div>

            </div>
    </div>
</x-app-layout>

<script>
    const buttons = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.dataset.tab;

            // reset
            buttons.forEach(b => {
                b.classList.remove('border-blue-500', 'text-blue-600');
                b.classList.add('text-gray-500');
            });

            contents.forEach(c => c.classList.add('hidden'));

            // active
            button.classList.add('border-b-2', 'border-blue-500', 'text-blue-600');
            document.getElementById(tab).classList.remove('hidden');
        });
    });
</script>

