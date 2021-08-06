import sys
import spacy
from   nltk.corpus import stopwords
from spacy.matcher import Matcher

stop_words = stopwords.words("english")
sys.path.append("./")
from   abbreviation_function  import abbreviation_handler
from   annotation_function    import *


# It create long term words based index
# Therefore, if we consider the long term "word1 word2 word3", the start pos is x, and s1, s2 are the spaces position,
# then:
#       word1 begin                        word2 begin               word3 begin
#    long_idx[x]     = x --> word1 begin, long[x + s1 + 1] = x, long_idx[x + s2 + 1] = x
#    idx_long[x]     = {x, x + s1 + 1, x + s2 + 1}
#    reverse_long[x] = word1 word2 word3
def create_long_term_idx(abbreviations_dict, document):
    long_idx     = dict()
    idx_long     = dict()
    reverse_long = dict()
    for span in abbreviations_dict:
        token = document[span.start]
        s_pos = token.idx
        long_idx[s_pos]     = s_pos
        idx_long[s_pos]     = {s_pos}
        reverse_long[s_pos] = span
        for idx in [i for i, ch in enumerate(span.text) if ch == " "]:
            pos_word = s_pos + idx + 1
            long_idx[pos_word] = s_pos
            idx_long[s_pos].add(pos_word)
    return  long_idx, reverse_long, idx_long


# It remove the stopwords or no alphanumeric character at the term start position
def long_term_text_elaboration(term_text, term_pos, term_components):
    # we are checking if the first character is a no alphanumeric term.
    # if it is true, this will be removed
    if not term_components[0][0].isalpha() or term_components[0].lower() in stop_words:
        term_pos  = term_pos + len(term_components[0]) + 1  # 1 is for a space
        term_text = " ".join(term_components[1:])
    return term_pos, term_text


# Spacy initialization
def spacy_init(article):
    nlp = spacy.load("en_core_web_sm")
    nlp.add_pipe("merge_entities")
    nlp.add_pipe("merge_noun_chunks")
    doc = nlp(article)
    return nlp, doc


def create_final_annotation(elaborated_annotation, annotation_to_add, annotation_to_remove):
    pos_annotation    = dict()
    final_annotations = list()
    for vpos, annotation in enumerate(elaborated_annotation):
        if vpos in annotation_to_remove: continue
        pos_annotation[annotation["start_pos"]] = annotation
    for annotation in annotation_to_add:
        pos_annotation[annotation["start_pos"]] = annotation
    for key in sorted(pos_annotation.keys()):
        final_annotations.append(pos_annotation[key])
    return final_annotations


def r(pos, op = None):
    return {"POS": pos, "OP" : op} if op is not None else {"POS": pos}


def verb_extraction(nlp, doc):
    matcher = Matcher(nlp.vocab)
    matcher.add("VERB", [[
        r("AUX", "?"), r("PART", "?"), r("AUX", "?"),
        r("ADP", "?"), r("ADV", "?"), r("VERB", "+"), r("ADP", "?")
    ]])
    matches  = matcher(doc)
    spans    = [doc[start:end] for _, start, end in matches]
    spans    = spacy.util.filter_spans(spans)
    verb_idx = {doc[pos].idx: span.start for span in spans for pos in range(span.start, span.end)}
    return verb_idx


# MAIN FUNCTION
def build_final_annotations(article, article_annotation):
    nlp, doc = spacy_init(article)
    long_short_occurrences = abbreviation_handler(nlp, doc)

    verb_idx = verb_extraction(nlp, doc)
    elaborated_annotations = joining_annotation(article_annotation, article, verb_idx)
    annotation_idx, rev_annotation = create_annotation_index(elaborated_annotations)
    abbreviation_idx, rev_abbreviation, idx_abbreviation = create_long_term_idx(long_short_occurrences, doc)

    # abbreviation correction
    annotation_to_remove = list()
    annotation_to_add    = list()
    for long_term, short_terms in long_short_occurrences.items():
        long_term_component = long_term.text.split(" ")
        # the system remove all the long term composed of more than 8 words (scispacy abbreviation error)
        if len(long_term_component) > 8: continue
        # ========================================= LONG TERM ELABORATION =====================================
        long_term_pos  = doc[long_term.start].idx
        annotation_pos = set()
        # words_pos contains the long term words position within the document
        words_pos      = idx_abbreviation[long_term_pos]
        # the system checks which annotations are into the long term
        for word_pos in words_pos:
            if word_pos not in annotation_idx: continue
            annotation_pos.add(annotation_idx[word_pos])
        annotation_pos = sorted(annotation_pos)
        # stopword and no alphanumeric term cleaning
        long_term_pos, long_term_text = long_term_text_elaboration(long_term.text, long_term_pos, long_term_component)
        # 1. several annotations are into the long term. the system create a new annotation, and then will remove
        #    the included ones.
        if len(annotation_pos) > 1 or len(annotation_pos) == 0:
            sel_annotation = create_annotation_struct_1(long_term_text, long_term_pos)
            annotation_to_add.append(sel_annotation)
            for annotation_id in annotation_pos:
                annotation_to_remove.append(rev_annotation[annotation_id]["vector_pos"])
        # 2. there is only a candidate annotation
        else:
            sel_annotation = elaborated_annotations[rev_annotation[annotation_pos[0]]["vector_pos"]]
            min_pos = min(long_term_pos, annotation_pos[0])
            max_pos = max(long_term_pos + len(long_term_text), sel_annotation["end_pos"])
            # if "start" pos and "end pos" of the long term and annotation are different, the system will update
            # the annotation using min_pos and max_pos
            if not (min_pos == sel_annotation["start_pos"] and max_pos == sel_annotation["end_pos"]):
                update_annotation_structure_1(article, min_pos, max_pos, sel_annotation)
        # =================================== SHORT TERM ELABORATION =========================================
        for short_term in short_terms:
            # the short term is also a tagme annotation
            short_term_pos = short_term[1]
            if short_term_pos in annotation_idx:
                selected_short_annotation = elaborated_annotations[rev_annotation[annotation_idx[short_term_pos]]["vector_pos"]]
                # if the annotation has a main word different that the long term, then the system will fix the tagme error
                if selected_short_annotation["Word"] == sel_annotation["Word"]: continue
                selected_short_annotation["Word"]       = sel_annotation["Word"]
                selected_short_annotation["categories"] = sel_annotation["categories"]
            # the short term is not a tagme annotation, but the complete one is.
            elif len(annotation_pos) > 0 :
                selected_short_annotation = create_annotation_struct_2(short_term[0], short_term_pos, sel_annotation)
                annotation_to_add.append(selected_short_annotation)
                # the short term and long one are not tagme annotation. the system will remove the temporary annotation
            else:
                annotation_to_add = annotation_to_add[:-1]
                break
    return create_final_annotation(elaborated_annotations, annotation_to_add, annotation_to_remove)
