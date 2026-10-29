<section class="container ps-lg-4 pe-lg-3 pt-4 mb-5" id="FaqArea">
    <div class="row">
        <div class="col-12 col-lg-6">
            <h2 class="h4 pb-3">سوالات متداول</h2>
            <div class="accordion accordion-flush" id="methods">
                @foreach($Faq as $key => $question)
                    <div class="accordion-item">
                        <label class="accordion-header">
                            <button class="accordion-button collapsed" href="#faq-{{$key}}"
                                    role="button" data-bs-toggle="collapse">
                               <h3 class="faq-h3"> {{$question['q']}}</h3></button>
                        </label>
                        <div class="accordion-collapse collapse" id="faq-{{$key}}"
                             data-bs-parent="#methods" style="">
                            <div class="card-body fs-md">
                                <p>{{$question['a']}}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
            <div class="col-6">
                <img src="{{secure_url('/')}}/image/FAQ.svg" class="gallery-item rounded-3" alt="Faq">
            </div>
        @endif
    </div>
</section>
