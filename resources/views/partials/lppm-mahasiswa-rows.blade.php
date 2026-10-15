{{-- Baris tabel LPPM Mahasiswa. Dipakai halaman utama & respons AJAX (LppmMahasiswaController::index). --}}
                                @php
                                $jenisBadge = [
                                'sinta_nasional' => ['label' => 'SINTA Nasional', 'class' => 'badge-info'],
                                'conference_internasional' => ['label' => 'Conference Int.', 'class' => 'badge-warning'],
                                'jurnal_internasional' => ['label' => 'Jurnal Int.', 'class' => 'badge-success'],
                                ];
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                @endphp
                                @foreach($items as $item)
                                @php
                                $nama = optional($item->mahasiswa->user)->name ?? 'Tanpa Nama';
                                $initials = collect(explode(' ', $nama))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $avatarColor = $colors[$item->mahasiswa_id % count($colors)];
                                $jb = $jenisBadge[$item->jenis] ?? ['label' => $item->jenis, 'class' => 'badge-neutral'];
                                $buktiLabel = $item->link_doi ? 'Link DOI' : 'Lihat Bukti';
                                $buktiIcon = $item->link_doi ? 'link' : 'link';
                                $buktiUrl = $item->link_doi ?: $item->bukti_kegiatan;
                                @endphp
                                <tr data-id="{{ $item->id }}" data-jenis="{{ $item->jenis }}">
                                    <td><span class="nim-code">{{ $item->mahasiswa->nim ?? '-' }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
                                            <div class="student-name"><span class="name">{{ $nama }}</span></div>
                                        </div>
                                    </td>
                                    <td><span class="activity-title" title="{{ $item->judul }}">{{ $item->judul }}</span>
                                        @include('partials.approval-chip', ['item' => $item])</td>
                                    <td class="center"><span class="badge {{ $jb['class'] }}">{{ $jb['label'] }}</span></td>
                                    <td class="center"><span class="badge badge-primary">{{ $item->peringkat ?: '-' }}</span></td>
                                    <td class="center"><span class="year-chip">{{ $item->tahun }}</span></td>
                                    <td class="center">
                                        <a href="{{ $buktiUrl }}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                                            <span class="material-symbols-outlined">{{ $buktiIcon }}</span>
                                            <span>{{ $buktiLabel }}</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-mahasiswa_id="{{ $item->mahasiswa_id }}"
                                                data-jenis="{{ $item->jenis }}"
                                                data-judul="{{ $item->judul }}"
                                                data-penulis="{{ $item->penulis }}"
                                                data-nama_jurnal="{{ $item->nama_jurnal ?? '' }}"
                                                data-peringkat="{{ $item->peringkat ?? '' }}"
                                                data-link_doi="{{ $item->link_doi ?? '' }}"
                                                data-bukti_kegiatan="{{ $item->bukti_kegiatan }}"
                                                data-tahun="{{ $item->tahun }}">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                            <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row" data-id="{{ $item->id }}">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach