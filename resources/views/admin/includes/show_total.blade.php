                                <h5>
                                    @if($rows->total() > 0)
                                        <?php 
                                        $total = $rows->total();
                                        if($rows->currentPage() > 1)
                                        {
                                            $startRow = ($rows->currentPage() * $rows->perPage()) - $rows->perPage() + 1;                            
                                            $endRow = $rows->currentPage() * $rows->perPage();                                                                
                                            
                                            if($endRow > $total)
                                            {
                                                $endRow = $total;
                                            }    
                                        }   
                                        else
                                        {
                                            $startRow = 1;                            
                                            $endRow = $rows->perPage();     
                                            if ($endRow > $total) {
                                                $endRow = $total;
                                            }
                                            
                                        }
                                        ?>
                                        Showing {{ $startRow }} to {{ $endRow }} of {{ number_format($total) }}
                                    @endif
                                </h5>
