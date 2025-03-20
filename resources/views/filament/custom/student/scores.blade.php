<div>
    <!-- Breathing in, I calm body and mind. Breathing out, I smile. - Thich Nhat Hanh -->
     @php
        $colors = ['text-blue-500', 'text-green-500'];
     @endphp
     @foreach($scores as $key => $score)

        <div 
            @class([
                $colors[$key],
                'font-bold',
                'text-lg',
                'text-center'
            ])
        >{{$score->$column}}</div>
     @endforeach
</div>
