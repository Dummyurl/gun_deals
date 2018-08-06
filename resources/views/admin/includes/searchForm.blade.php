<form class="sky-form seach_user_form" method="get">
    {!! csrf_field() !!}
    <header>
        Search {{ $moduleTitle }}
        @if(isset($add_url) && $add_url != "")
            <a class="btn-u btn-u-dark pull-right" href="{{ $add_url }}">Add New</a>
        @endif
    </header>

    <fieldset>
        <input type="hidden" name="record_per_page" id="record_per_page" value="{{ Request::get('record_per_page') }}"/>
        <div class="row">
            @if(isset($with_date) && $with_date == 1)            
                <section class="col col-2">
                    <label class="label">From Date</label>
                    <label class="input">
                        <input type="text" name="from_date" value="{{ Request::get('from_date') }}" id="start_date">
                        <i class="icon-append fa fa-calendar"></i>
                    </label>
                </section> 
                <section class="col col-2">
                    <label class="label">To Date</label>
                    <label class="input">
                        <input type="text" name="to_date" value="{{ Request::get('to_date') }}" id="end_date">
                        <i class="icon-append fa fa-calendar"></i>
                    </label>
                </section>             

                <section class="col col-3">
                    <label class="label">Search By</label>
                    <label class="select">
                        <select name="search_field">
                            <option value="">select field</option>
                            @foreach($searchColumns as $column => $title)
                                <option value="{{ $column }}" {{ Request::get('search_field') == $column ? 'selected="selected"' : "" }}>
                                    {{ $title }}
                                </option>                        
                            @endforeach         
                        </select>
                    </label>
                </section>
                <section class="col col-3">
                    <label class="label">Search Text</label>
                    <label class="input">
                        <input type="text" name="search_text" value="{{ Request::get('search_text') }}">
                    </label>                    
                </section>     
                
                @if(isset($show_checkbox) && $show_checkbox == 1)
                    <div class='clearfix'>&nbsp;</div>
                    <section class="col col-4">
                        <label class="label">&nbsp;</label>
                        <label>
                            <input type="checkbox" name='only_donations' value="1" {{ Request::get('only_donations') == 1 ? 'checked="checked"':'' }}/>
                            View Donations Project Only
                        </label>
                    </section>
                    <section class="col col-2">
                        <label class="label">&nbsp;</label>
                        <label>
                            <a class="btn-u btn-u-red pull-right" href="{{ $list_url }}" style="margin-left: 10px;">Reset</a>                                                
                            <button class="btn-u pull-right" type="submit">Search</button>                          
                        </label>
                    </section>                            
                    <div class='clearfix'>&nbsp;</div>
                @else
                    <section class="col col-2">
                        <label class="label">&nbsp;</label>
                        <label>
                            <a class="btn-u btn-u-red pull-right" href="{{ $list_url }}" style="margin-left: 10px;">Reset</a>                                                
                            <button class="btn-u pull-right" type="submit">Search</button>                          
                        </label>
                    </section>                            
                @endif
            @else
                <section class="col col-4">
                    <label class="label">Search By</label>
                    <label class="select">
                        <select name="search_field">
                            <option value="">select field</option>
                            @foreach($searchColumns as $column => $title)
                                <option value="{{ $column }}" {{ Request::get('search_field') == $column ? 'selected="selected"' : "" }}>
                                    {{ $title }}
                                </option>                        
                            @endforeach                                     
                        </select>
                    </label>
                </section>
                <section class="col col-4">
                    <label class="label">Search Text</label>
                    <label class="input">
                        <input type="text" name="search_text" value="{{ Request::get('search_text') }}">
                    </label>                    
                </section>                     

                <section class="col col-3">
                    <label class="label">&nbsp;</label>
                    <label>
                            <a class="btn-u btn-u-red pull-right" href="{{ $list_url }}" style="margin-left: 10px;">Reset</a>                                                
                            <button class="btn-u pull-right" type="submit">Search</button>                          
                    </label>
                </section>            
            @endif
        </div>
    </fieldset>

</form>        